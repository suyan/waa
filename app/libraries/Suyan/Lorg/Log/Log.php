<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 20:42:08
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 17:11:43
*/
namespace Suyan\Lorg\Log;

class Log
{
    protected $log;
    public function __construct($log){
        $log = 'Suyan\Lorg\Log\\'.$log.'Log';
        $this->log = new $log;
    }

    public function init($source = ''){
        return $this->log->init($source);
    }

    public function log($message){
        return $this->log->log($message);
    }

    public function logExit($message){
        return $this->log->logExit($message);
    }

    public function logProcess($process){
        return $this->log->logProcess($process);
    }
}
