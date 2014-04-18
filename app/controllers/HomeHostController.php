<?php
/*
 * @Author: Su Yan <http://yansu.org>
 * @Date:   2014-03-18 11:04:30
 * @Last Modified by:   Su Yan
 * @Last Modified time: 2014-04-18 22:54:54
*/

class HomeHostController extends HomeController
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
                'url' => 'home/host/host',
                'class' => ''
            )
        );
    }
    
    public function getHost()
    {
        $this->leftNav['host']['class'] = 'active';
        
        $hosts = Host::where('user_id', Auth::user()->id)->paginate(Config::get('waa.paginate'));
        Host::refreshStatus($hosts);

        return View::make('home.host.host')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.host'))
            ->with('hosts', $hosts);
    }

    /**
     * 由Ajax调用，获得指定主机的状态
     * @param  string $ids 类似"1,2,3,5"字符串，用户的一系列主机id
     * @return json   主机数组，数据内是主机对象{id:xxx,process:xxx,status:xxx}
     */
    public function getHostByIds($ids)
    {
        if (Request::ajax()) {
            $response = array(
                'code' => '0',
                'hosts' => array()
                );
            // 数据为空直接返回
            if(empty($ids)) return Response::json($response);
            
            $ids = explode(',', $ids);
            if (is_array($ids)) {
                // 获得所需的主机
                $hosts = DB::table('hosts')
                    ->select('id','process','log','status','pid')
                    ->where('user_id', Auth::user()->id)
                    ->whereIn('id', $ids)
                    ->get();

                Host::refreshStatus($hosts);

                // 删除不必要的字段
                array_walk($hosts, function(&$value, $key){
                    unset($value->pid);
                });

                $response['code'] = 1;
                $response['hosts'] = $hosts;
                return Response::json($response);
            } else {
                return Response::json($response);    
            }
            
        }
        return Redirect::to('home/host/host');
    }
    
    public function getCreate()
    {
        $this->leftNav['host']['class'] = 'active';
        
        return View::make('home.host.create')
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
            
            return Redirect::to('home/host/create')->with('errors', $errors);
        }
        
        $file = Input::file('uploadfile');
        $host->file_name = md5(uniqid('', true));
        $host->file_size = $file->getSize();
        $host->file_md5 = md5_file($file->getRealPath());
        $file->move(Config::get('waa.upload_dir') , $host->file_name);
        $host->user_id = Auth::user()->id;
        $host->save();
        
        return Redirect::to('home/host/host');
    }

    public function getDelete($host)
    {
        $this->leftNav['host']['class'] = 'active';

        return View::make('home.host.delete')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.delete'))
            ->with('host', $host);
    }

    /**
     * 删除整个主机，包括主机host条目以及相应的vector分析结果
     * @param  int $host host的id
     */
    public function postDelete($host)
    {
        // 判断是否是主机主人
        $user_id = Auth::user()->id;
        $host = Host::find($host);

        if($host->user_id != $user_id){
            $error = Lang::get('host.not_owner');
            return Redirect::to('home/host/host')
                ->with('error', $error);
        } else {
            //删除主机，并且删除文件
            File::delete(Config::get('waa.upload_dir').'/'.$host->file_name);
            DB::table('vectors')->where('host_id',$host->id)->delete();
            $host->delete();
            return Redirect::to('home/host/host');
        }
    }

    public function getRun($host)
    {
        $this->leftNav['host']['class'] = 'active';

        return View::make('home.host.run')
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
            return Redirect::to('home/host/host')
                ->with('error', $error);
        } else {
            // 开始分析
            $host->status = 1; //进入队列
            $host->save();
            Queue::push('LorgQueue', array('host_id' => $host->id));
            return Redirect::to('home/host/host');
        }
    }

    public function getInfo($host){
        $this->leftNav['host']['class'] = 'active';

        // 判断是否是主机主人
        $user_id = Auth::user()->id;
        $host = DB::table('hosts')->where('id', $host)->first();

        if($host->user_id != $user_id){
            $error = Lang::get('host.not_owner');
            return Redirect::to('home/host/host')
                ->with('error', $error);
        } else {
            $vectors = DB::table('vectors')
                ->where('host_id', $host->id)
                ->where('impact', '>' , Config::get('waa.detect.threshold'))
                ->get();

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


            return View::make('home.host.info')
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

    public function getVector($host){
        $this->leftNav['host']['class'] = 'active';

        $user_id = Auth::user()->id;
        $host = DB::table('hosts')->where('id', $host)->first();

        if($host->user_id != $user_id){
            $error = Lang::get('host.not_owner');
            return Redirect::to('home/host/host')
                ->with('error', $error);
        } else {
            $vectors = Vector::where('host_id',$host->id)
                ->orderBy('impact', 'desc')
                ->paginate(Config::get('waa.paginate'));

            return View::make('home.host.vector')
                ->with('title', Lang::get('admin.host'))
                ->with('leftNav', $this->leftNav)
                ->with('hostId', $host->id)
                ->with('vectors', $vectors);
        }
    }

}
