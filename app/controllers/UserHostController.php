<?php
/*
 * @Author: Su Yan <http://yansu.org>
 * @Date:   2014-03-18 11:04:30
 * @Last Modified by:   Su Yan
 * @Last Modified time: 2014-03-26 10:52:29
*/

class UserHostController extends HomeController
{
    public $leftNav;
    
    public function __construct()
    {
        parent::__construct();
        
        //set top nav
        $this->topNav['host']['class'] = 'active';
        View::share('topNav', $this->topNav);
        //set left nav
        $this->leftNav = array(
            'host' => array(
                'name' => 'host.host',
                'url' => 'host/host',
                'class' => ''
            )
        );
    }
    
    public function getHost()
    {
        $this->leftNav['host']['class'] = 'active';
        
        $hosts = Host::where('user_id', Auth::user()->id)->paginate(Config::get('app.paginate'));

        return View::make('user.host.host')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.host'))
            ->with('hosts', $hosts);
    }
    
    public function getCreate()
    {
        $this->leftNav['host']['class'] = 'active';
        
        return View::make('user.host.create')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.create'));
    }
    
    public function postCreate()
    {
        if (!Input::hasFile('uploadfile')) $uploadfile = Lang::get('host.no_file');
        
        $host = new Host;
        $host->hostname = Input::get('hostname');
        $host->domain = Input::get('domain');
        $host->description = Input::get('description');
        
        if (false == $host->validate() || isset($uploadfile)) {
            $errors = $host->errors();
            if (isset($uploadfile)) $errors->add('uploadfile', $uploadfile);
            
            return Redirect::to('host/create')->withErrors($errors);
        }
        
        $file = Input::file('uploadfile');
        $host->file_name = md5(uniqid('', true));
        $host->file_size = $file->getSize();
        $host->file_md5 = md5_file($file->getRealPath());
        $file->move(Config::get('app.upload_dir') , $host->file_name);
        $host->user_id = Auth::user()->id;
        $host->save();
        
        return Redirect::to('host/host');
    }

    public function getDelete($host)
    {
        $this->leftNav['host']['class'] = 'active';

        return View::make('user.host.delete')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.delete'))
            ->with('host', $host);
    }

    public function postDelete($host)
    {
        // 判断是否是主机主人
        $user_id = Auth::user()->id;
        $host = Host::find($host);

        if($host->user_id != $user_id){
            $error = Lang::get('host.not_owner');
            return Redirect::to('host/host')
                ->with('error', $error);
        } else {
            //删除主机，并且删除文件
            File::delete(Config::get('app.upload_dir').'/'.$host->file_name);
            DB::table('vectors')->where('host_id',$host->id)->delete();
            $host->delete();
            return Redirect::to('host/host');
        }
    }

    public function getRun($host)
    {
        $this->leftNav['host']['class'] = 'active';

        return View::make('user.host.run')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.run'))
            ->with('host', $host);
    }

    public function postRun($host)
    {
        // 判断是否是主机主人
        $user_id = Auth::user()->id;
        $host = Host::find($host);

        if($host->user_id != $user_id){
            $error = Lang::get('host.not_owner');
            return Redirect::to('host/host')
                ->with('error', $error);
        } else {
            // 开始分析
            $host->status = 1; //进入队列
            $host->save();
            Queue::push('LorgQueue', array('host_id' => $host->id));
            return Redirect::to('host/host');
        }
    }

    public function getInfo($host){
        $this->leftNav['host']['class'] = 'active';

        // 判断是否是主机主人
        $user_id = Auth::user()->id;
        $host = DB::table('hosts')->where('id', $host)->first();

        if($host->user_id != $user_id){
            $error = Lang::get('host.not_owner');
            return Redirect::to('host/host')
                ->with('error', $error);
        } else {
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

            return View::make('user.host.info')
                ->with('host', $host)
                ->with('leftNav', $this->leftNav)
                ->with('title', Lang::get('host.info'))
                ->with('cityAttackData', json_encode($cityAttackData))
                ->with('cityAttackLocation', json_encode($cityAttackLocation))
                ->with('countryAttackCount', json_encode($country));
            
        }
    }

}
