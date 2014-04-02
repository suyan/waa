<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 20:14:35
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-01 18:52:51
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
            'sites' => array(
                'name' => 'admin.setting.app',
                'url' => 'admin/setting',
                'class' => ''),
            'lorgs' => array(
                'name' => 'admin.setting.lorg',
                'url' => 'admin/setting/lorgs',
                'class' => ''),
            'regexes' => array(
                'name' => 'admin.setting.regex',
                'url' => 'admin/setting/regexes',
                'class' => ''),
        );
    }

    public function getSites(){
        $this->leftNav['sites']['class'] = 'active';

        $settings = Setting::where('group','waa')->paginate(Config::get('waa.paginate'));

        return View::make('admin.setting.sites')
            ->with('title', Lang::get('admin.setting.app'))
            ->with('leftNav', $this->leftNav)
            ->with('settings', $settings);
    }

    public function getLorgs(){
        $this->leftNav['lorgs']['class'] = 'active';

        $settings = Setting::where('group','lorg')->paginate(Config::get('waa.paginate'));

        return View::make('admin.setting.lorgs')
            ->with('title', Lang::get('admin.setting.lorg'))
            ->with('leftNav', $this->leftNav)
            ->with('settings', $settings);
    }

    public function getRegexes(){
        $this->leftNav['regexes']['class'] = 'active';

        $settings = json_decode(File::get(Config::get('waa.phpids.phpidsFilterPath')),true);

        return View::make('admin.setting.regexes')
            ->with('title', Lang::get('admin.setting.lorg'))
            ->with('leftNav', $this->leftNav)
            ->with('settings', $settings['filters']['filter']);   
    }
}