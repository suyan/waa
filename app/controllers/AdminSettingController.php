<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 20:14:35
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-27 16:37:46
*/
class AdminSettingController extends AdminController
{

    public $leftNav;

    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['setting']['class'] = 'active';
        View::share('topNav', $this->topNav);

        //set left nav
        $this->leftNav = array(
            'setting' => array(
                'name' => 'admin.all_setting',
                'url' => 'admin/setting',
                'class' => ''
            )
        );
    }

    public function getSetting(){
        $this->leftNav['setting']['class'] = 'active';

        $settings = Setting::paginate(Config::get('app.paginate'));

        return View::make('admin.setting.setting')
            ->with('title', Lang::get('admin.setting'))
            ->with('leftNav', $this->leftNav)
            ->with('settings', $settings);
    }
}