<?php

use Indigo\Supervisor\Supervisor;
use Indigo\Supervisor\Process;
use Indigo\Supervisor\Connector;

class Host extends \LaravelBook\Ardent\Ardent
{
    public static $rules = array(
        'hostname' => 'required|alpha_dash|between:4,20',
        'domain' => 'required|between:2,50', //TODO
        'description' => 'required|between:2,100',
    );
    
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * 获得Supervisor对象
     * @return Supervisor Supervisor对象
     */
    static public function getSupervisor(){
        // Supervisor不需要持久链接，不用单例模式，可以重复生成
        $connector = new Connector\InetConnector(
            Config::get('waa.supervisor.host'), 
            Config::get('waa.supervisor.port')
            );
        $connector->setCredentials(
            Config::get('waa.supervisor.name'),
            Config::get('waa.supervisor.password')
            );
        return new Supervisor($connector);
    }

    /**
     * 刷新已经死掉的分析进程所对应的主机状态
     * @param  array $hosts 主机对量列表数组，由model直接获得的hosts即可
     */
    static public function refreshStatus($hosts){
        // 获得所有的supervisor进程pid
        $super = Host::getSupervisor();
        $processes = $super->getAllProcessInfo();
        $pids = array();
        foreach ($processes as $process) 
            array_push($pids, $process['pid']);

        // 遍历正在进行的主机，查看主机pid是否还在执行
        foreach ($hosts as $host) {
            if ($host->status == 2) {
                if (!in_array($host->pid, $pids))
                    Host::where('id', $host->id)->update(array('status'=>4));
            }
        }
    }

}
