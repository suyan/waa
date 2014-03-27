<?php
/*
 * @Author: Su Yan <http://yansu.org>
 * @Date:   2014-03-22 18:45:32
 * @Last Modified by:   Su Yan
 * @Last Modified time: 2014-03-24 17:11:56
*/
namespace Suyan\Lorg\Log;
interface LogInterface {
    public function init($source = '');
    public function log($message);
    public function logExit($message);
    public function logProcess($process);
}
