<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:37:19
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 22:43:09
*/
namespace Suyan\Lorg\Config;

class FileConfig implements ConfigInterface {

    protected $fileName;
    protected $configs;

    public function init($source = ''){
        if(file_exists($source))
            $this->fileName = $source;
        else 
            throw new \Exception('config '.$source.' not exist!');

        if(fopen($this->fileName,'r') == false)
            throw new \Exception('config '.$source.' is not readable!');

        $this->configs = require($this->fileName);
    }

    public function get($name, $default = ''){
        if(isset($this->configs[$name]))
            return $this->configs[$name];
        elseif($default)
            return $default;
        else 
            return false;
    }

    public function set($name, $value){
        return $this->configs[$name] = $value;
    }
}

