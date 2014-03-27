<?php
class AdminController extends BaseController
{
    public $topNav;
    
    public function __construct()
    {
        parent::__construct();
        $this->beforeFilter('auth.admin');

        $this->topNav = array(
            'home'  => array(
                'name' => 'admin.home', 
                'url' => 'admin', 
                'class' => ''),
            'host' => array(
                'name' => 'admin.host',
                'url' => 'admin/host',
                'class' => ''),
            'task' => array(
                'name' => 'admin.task',
                'url' => 'admin/task',
                'class' => ''),
            'user' => array(
                'name' => 'admin.user',
                'url' => 'admin/user',
                'class' => ''),
            'setting' => array(
                'name' => 'admin.setting',
                'url' => 'admin/setting',
                'class' => '')
        );
    }

    public function missingMethod($parameters = array())
    {
        return 'not found';
    }
}
