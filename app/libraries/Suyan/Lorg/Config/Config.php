<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:35:57
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 22:44:06
*/
namespace Suyan\Lorg\Config;
class Config implements ConfigInterface
{    
    protected $config;
    public function __construct($config){
        $config = 'Suyan\Lorg\Config\\'.$config.'Config';
        $this->config = new $config;
    }

    public function init($source = ''){
        return $this->config->init($source);
    }

    public function get($name, $default = ''){
        return $this->config->get($name, $default);
    }

    public function set($name, $value){
        return $this->config->set($name, $value);
    }
}