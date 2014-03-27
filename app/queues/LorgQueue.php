<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-20 09:53:53
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-25 21:31:45
*/
class LorgQueue
{
    public function fire($job, $data)
    {
        $host_id = $data['host_id'];
        $host = Host::find($host_id);
        $host->status = 2;
        $host->start_time = date('Y-m-d H:i:s', time());
        $host->save();
        

        // 删除本主机上次的分析结果
        DB::table('vectors')->where('host_id',$host->id)->delete();
        
        // 进行分析主机
        $lorg = new Suyan\Lorg\Lorg('File', app_path().'/libraries/Suyan/Lorg/Data/config.php');
        $lorg->config->set('input', array(
            'default'=>'File', 
            'File'=>app_path().'/storage/upload/'.$host->file_name
            ));
        $lorg->config->set('output', array('default'=>'Db', 
            'Db'=> array(
                'vector_db' => DB::table('vectors'),
                'host_db' => DB::table('hosts'),
                'host_id' => $host->id
            )));
        $lorg->config->set('log', array('default'=>'Db',
            'Db' => array(
                'host_db' => DB::table('hosts'),
                'host_id' => $host->id
                )
            ));
        $lorg->run();

        $host->status = 3;
        $host->save();
        $job->delete();
    }
}