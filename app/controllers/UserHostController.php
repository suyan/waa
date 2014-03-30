<?php
/*
 * @Author: Su Yan <http://yansu.org>
 * @Date:   2014-03-18 11:04:30
 * @Last Modified by:   Su Yan
 * @Last Modified time: 2014-03-29 20:51:23
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
        
        $hosts = Host::where('user_id', Auth::user()->id)->paginate(Config::get('waa.paginate'));

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
        $file->move(Config::get('waa.upload_dir') , $host->file_name);
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
            File::delete(Config::get('waa.upload_dir').'/'.$host->file_name);
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

            //构造geoip图所需变量
            
            // 根据client将攻击分组
            $clients = array(); // 地点信息
            $country_impact_count = array(); // 国家影响
            $country_attack_count = array(); // 国家攻击总数
            $clients_attack_count = 0; // client总数
            $countries_attack_count = 0;

            foreach($vectors as $vector){
                if(isset($clients[$vector->client])){
                    $clients[$vector->client]['latLng'] = $vector->location;
                    $clients[$vector->client]['impact_count'] += $vector->impact;
                    $clients[$vector->client]['request_count'] += 1;
                    if($vector->impact > 10){
                        $clients[$vector->client]['attack_count'] += 1;
                        $clients_attack_count += 1;
                    }
                }else{
                    $clients[$vector->client]['latLng'] = $vector->location;
                    $clients[$vector->client]['impact_count'] = 0 + $vector->impact;
                    $clients[$vector->client]['request_count'] = 1;
                    if($vector->impact > 10){
                        $clients[$vector->client]['attack_count'] = 1;
                        $clients_attack_count += 1;
                    } else 
                        $clients[$vector->client]['attack_count'] = 0;
                }
                if(isset($country_impact_count[$vector->remote_code])){
                    $country_impact_count[$vector->remote_code] += $vector->impact;
                    if($vector->impact > 10){
                        $country_attack_count[$vector->remote_code] += 1;
                        $countries_attack_count += 1;
                    }
                } else {
                    $country_impact_count[$vector->remote_code] = 0 + $vector->impact;
                    if($vector->impact > 10){
                        $country_attack_count[$vector->remote_code] = 1;
                        $countries_attack_count += 1;
                    } else 
                        $country_attack_count[$vector->remote_code] = 0;
                }
            }

            $countryImpactCount = array();
            $cityAttackLocation = array();
            $clientImpactRate = array();
            foreach($clients as $key => $client){
                $cityAttackData[] = $client['impact_count'];  
                $cityAttackLocation[] = array(
                    'latLng' => explode(',', $client['latLng']),
                    'name' => $key);
                $clientImpactRate[] = array($key, 
                    ceil($client['impact_count']*100/$host->impact_count));
            }

            //饼状图只留前4个+others
            if (count($clientImpactRate) > 5) {
                $clientImpactRate = array_slice($clientImpactRate, 0, 4);
                $sum = $clientImpactRate[0][1] + $clientImpactRate[1][1] +
                    $clientImpactRate[2][1] + $clientImpactRate[3][1];
                $clientImpactRate[] = array('others', 100 - $sum);
            }


            return View::make('admin.host.info')
                ->with('host', $host)
                ->with('title', Lang::get('host.info'))
                ->with('leftNav', $this->leftNav)
                ->with('vectors', $vectors)
                ->with('cityAttackData', json_encode($cityAttackData))
                ->with('cityAttackLocation', json_encode($cityAttackLocation))
                ->with('countryImpactCount', json_encode($country_impact_count))
                ->with('countryAttackCount', json_encode($country_attack_count))
                ->with('clientImpactRate', json_encode($clientImpactRate));
            
        }
    }

}
