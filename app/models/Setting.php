<?php
class Setting extends \LaravelBook\Ardent\Ardent
{
    static public function setWaaConfigs(){
        // 从数据库中取到所有配置，覆盖配置文件中的配置
        $settings = DB::table('settings')->where('group','waa')->get();
        foreach($settings as $setting){
            Config::set('waa.'.$setting->name, $setting->value);
        }
    }
}