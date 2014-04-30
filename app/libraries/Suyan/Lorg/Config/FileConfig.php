<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:37:19
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-30 10:22:28
*/
namespace Suyan\Lorg\Config;

class FileConfig implements ConfigInterface {

    protected $fileName;
    protected $configs;

    public function __construct($source = ''){
        if(file_exists($source))
            $this->fileName = $source;
        else 
            throw new \Exception('config '.$source.' not exist!');

        if(fopen($this->fileName,'r') == false)
            throw new \Exception('config '.$source.' is not readable!');
        // 读取文件中的配置
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

