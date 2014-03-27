<?php
class Setting extends \LaravelBook\Ardent\Ardent
{
    static public function setAllConfigs(){
        $settings = DB::table('settings')->get();
        foreach($settings as $setting){
            Config::set($setting->name, $setting->value);
        }
    }
}