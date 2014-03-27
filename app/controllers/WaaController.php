<?php
class WaaController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
        $this->topNav = array(
            'home'  => array(
                'name' => 'home.home', 
                'url' => '/', 
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
        // set_time_limit(0);
        // echo "<meta charset='utf-8'/>";
        
        // $lorg = new Suyan\Lorg\Lorg('File', app_path().'/libraries/Suyan/Lorg/Data/config.php');
        // $lorg->config->set('input', array('default'=>'File', 'File'=>app_path().'/libraries/Suyan/Lorg/Data/log_test'));
        // $lorg->config->set('output', array('default'=>'Db', 
        //     'Db'=> array(
        //         'vector_db' => DB::table('vectors'),
        //         'host_db' => DB::table('hosts'),
        //         'host_id' => 1
        //     )));
        // $lorg->config->set('log', array('default'=>'Db',
        //     'Db' => array(
        //         'host_db' => DB::table('hosts'),
        //         'host_id' => 1
        //         )
        //     ));
        // $lorg->run();
    }
}
