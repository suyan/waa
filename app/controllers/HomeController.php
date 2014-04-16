<?php
class HomeController extends BaseController
{
    public $topNav;
    
    public function __construct()
    {
        parent::__construct();
        $this->beforeFilter('auth');

        $this->topNav = array(
            'home'  => array(
                'name' => 'home.home', 
                'url' => 'home', 
                'class' => ''),
            'host' => array(
                'name' => 'host.host', 
                'url' => 'home/host/host', 
                'class' => ''),
            'activity' => array(
                'name' => 'home.activity', 
                'url' => 'home/activity', 
                'class' => ''),
            // 'about' => array(
            //     'name' => 'home.about', 
            //     'url' => 'about', 
            //     'class' => ''),
        );
    }

    public function missingMethod($parameters = array())
    {
        return 'not found';
    }
}
