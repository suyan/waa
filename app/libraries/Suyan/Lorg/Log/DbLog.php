<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 20:46:13
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 17:07:09
*/
namespace Suyan\Lorg\Log;
class DbLog implements LogInterface
{

    protected $host_db;
    protected $host_id;

    public function init($source = '')
    {
        $this->host_db = $source['host_db'];
        $this->host_id = $source['host_id'];
        return true;
    }

    public function log($message)
    {
        $this->host_db->where('id',$this->host_id)->update(array(
            'log' => $message
            ));
    }

    public function logExit($message)
    {
        $this->host_db->where('id',$this->host_id)->update(array(
            'log' => $message
            ));
        exit();
    }

    public function logProcess($process)
    {
        $this->host_db->where('id',$this->host_id)->update(array(
            'process' => $process
            ));
    }

}
