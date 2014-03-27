<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 20:14:35
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-26 10:48:44
*/
class AdminHostController extends AdminController
{

    public $leftNav;

    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['host']['class'] = 'active';
        View::share('topNav', $this->topNav);

        //set left nav
        $this->leftNav = array(
            'host' => array(
                'name' => 'host.all_host',
                'url' => 'admin/host',
                'class' => ''
            )
        );
    }

    public function getHost(){
        $this->leftNav['host']['class'] = 'active';

        $hosts = Host::paginate(Config::get('app.paginate'));
        return View::make('admin.host.host')
            ->with('title', Lang::get('admin.host'))
            ->with('leftNav', $this->leftNav)
            ->with('hosts', $hosts);
    }

    public function getDelete($host)
    {
        $this->leftNav['host']['class'] = 'active';

        return View::make('admin.host.delete')
            ->with('title', Lang::get('host.delete'))
            ->with('leftNav', $this->leftNav)
            ->with('host', $host);
    }

    public function postDelete($host)
    {
        $host = Host::find($host);
        //删除主机，并且删除文件
        File::delete(Config::get('app.upload_dir').'/'.$host->file_name);
        DB::table('vectors')->where('host_id',$host->id)->delete();
        $host->delete();
        return Redirect::to('admin/host');

    }

    public function getRun($host)
    {
        $this->leftNav['host']['class'] = 'active';
        return View::make('admin.host.run')
            ->with('title', Lang::get('host.run'))
            ->with('leftNav', $this->leftNav)
            ->with('host', $host);
    }

    public function postRun($host)
    {
        $host = Host::find($host);
        // 开始分析
        $host->status = 1; //进入队列
        $host->save();
        Queue::push('LorgQueue', array('host_id' => $host->id));
        return Redirect::to('host/host');
    }

    public function getInfo($host){
        $this->leftNav['host']['class'] = 'active';

        $host = DB::table('hosts')->where('id', $host)->first();

        $vectors = DB::table('vectors')->where('host_id', $host->id)->get();

        $locations = array();
        $country = array();

        foreach($vectors as $vector){
            if(isset($locations[$vector->client])){
                $locations[$vector->client]['latLng'] = $vector->location;
                $locations[$vector->client]['impact'] += $vector->impact;
            }else{
                $locations[$vector->client]['latLng'] = $vector->location;
                $locations[$vector->client]['impact'] = 0 + $vector->impact;
            }
            if(isset($country[$vector->remote_code]))
                $country[$vector->remote_code] += $vector->impact;
            else
                $country[$vector->remote_code] = 0 + $vector->impact;
        }

        $countryAttackCount = array();
        $cityAttackLocation = array();
        foreach($locations as $key => $location){
            $cityAttackData[] = $location['impact'];  
            $cityAttackLocation[] = array(
                'latLng' => explode(',', $location['latLng']),
                'name' => $key);
        }

        return View::make('admin.host.info')
            ->with('host', $host)
            ->with('title', Lang::get('host.info'))
            ->with('leftNav', $this->leftNav)
            ->with('cityAttackData', json_encode($cityAttackData))
            ->with('cityAttackLocation', json_encode($cityAttackLocation))
            ->with('countryAttackCount', json_encode($country));
        
    }
}