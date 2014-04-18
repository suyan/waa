<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-20 09:53:53
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-17 10:37:00
*/
class LorgQueue
{
    public function fire($job, $data)
    {
        $job->delete(); 
        //关闭数据库查询缓存
        DB::connection()->disableQueryLog();
        $host_id = $data['host_id'];
        $host = Host::find($host_id);
        $host->status = 2;
        $host->pid = posix_getpid();
        $host->start_time = date('Y-m-d H:i:s', time());
        $host->save();
        
        // 删除本主机上次的分析结果
        DB::table('vectors')->where('host_id',$host->id)->delete();
        
        // 进行分析主机
        $lorg = new Suyan\Lorg\Lorg('Laravel', 'waa');

        $lorg->config->set('input', array(
            'default'=>'File', 
            'File'=>app_path().'/storage/upload/'.$host->file_name
            ));

        $lorg->config->set('output', array(
            'default'=>'Laravel', 
            'Laravel'=> array(
                'vector_db' => DB::table('vectors'),
                'host_db' => DB::table('hosts'),
                'host_id' => $host->id
            )));

        $lorg->config->set('log', array(
            'default'=>'Laravel',
            'Laravel' => array(
                'host_db' => DB::table('hosts'),
                'host_id' => $host->id
                )
            ));

        // xhprof
        include_once '/usr/local/Cellar/php55-xhprof/254eb24/xhprof_lib/utils/xhprof_lib.php';
        include_once '/usr/local/Cellar/php55-xhprof/254eb24/xhprof_lib/utils/xhprof_runs.php';
         
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
         
        $lorg->run();
         
        $xhprof_data = xhprof_disable();
         
        $profiler_namespace="hello";
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

        $host->status = 3;
        $host->save();
    }
}