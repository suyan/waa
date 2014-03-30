<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 20:46:13
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 20:47:41
*/
namespace Suyan\Lorg\Log;
class TerminalLog implements LogInterface
{
    public function init($source = '')
    {
        return true;
    }
    public function log($message)
    {
        echo $message, "\n";
    }
    public function logExit($message)
    {
        exit($message);
    }

    public function logProcess($process){
        echo $process, "\r";
    }
}
