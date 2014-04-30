<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:37:19
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-30 10:23:20
*/
namespace Suyan\Lorg\Config;

class LaravelConfig implements ConfigInterface {

    protected $configName;
    protected $configs;

    public function __construct($source = ''){
        $this->configName = $source;
        // 读取文件中的配置
        // $this->configs = require($this->fileName);
        $this->configs = \Illuminate\Support\Facades\Config::get($this->configName);
        // 读取数据库中的配置
        $settings = \Illuminate\Support\Facades\DB::table('settings')->where('group','lorg')->get();
        foreach($settings as $setting){
            \Illuminate\Support\Facades\Config::set('waa.'.$setting->name, json_decode($setting->value));
        }
    }

    public function get($name, $default = ''){
        return $this->configs = 
            \Illuminate\Support\Facades\Config::get($this->configName.'.'.$name, $default);
    }

    public function set($name, $value){
        \Illuminate\Support\Facades\Config::set($this->configName.'.'.$name, $value);
    }
}

