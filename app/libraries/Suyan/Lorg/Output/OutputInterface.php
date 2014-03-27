<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:39:58
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 22:39:41
*/
namespace Suyan\Lorg\Output;
interface OutputInterface {
    public function init($source = '');
    public function writeVector($vector);
    public function writeSummarize($summarize);
}