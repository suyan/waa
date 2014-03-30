<?php
use Indigo\Supervisor\Supervisor;
use Indigo\Supervisor\Process;
use Indigo\Supervisor\Connector;
class WaaController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
        $this->topNav = array(
            'home'  => array(
                'name' => 'home.home', 
                'url' => 'about', 
                'class' => ''),
            // 'about' => array(
            //     'name' => 'home.about', 
            //     'url' => 'about', 
            //     'class' => ''),
        );
        // $this->topNav['about']['class'] = 'active';
        View::share('topNav', $this->topNav);
    }
    
    public function getAbout()
    {
        return View::make('pages.about')->with('title', Lang::get('home.about'));
    }

    public function getDemo(){
        // $connector = new Connector\InetConnector(
        //     Config::get('waa.supervisor_host'), 
        //     Config::get('waa.supervisor_port')
        //     );
        // $connector->setCredentials(
        //     Config::get('waa.supervisor_name'),
        //     Config::get('waa.supervisor_password'));
        // $supervisor = new Supervisor($connector);
        // $process = $supervisor->getProcess('waaQueue:waaQueue_0');
        // var_dump($process);
    }
}
