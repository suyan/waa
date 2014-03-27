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
                'url' => '/', 
                'class' => ''),
            'host' => array(
                'name' => 'host.host', 
                'url' => 'host/host', 
                'class' => ''),
            'activity' => array(
                'name' => 'home.activity', 
                'url' => 'activity', 
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
