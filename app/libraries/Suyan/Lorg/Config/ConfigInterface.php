<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:39:58
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-30 10:21:53
*/
namespace Suyan\Lorg\Config;
interface ConfigInterface {
    public function get($name, $default = '');
    public function set($name, $value);
}