<?php
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
        );
        View::share('topNav', $this->topNav);
    }
    
    // index page of site
    public function getIndex()
    {
        if (Auth::guest())
            return Redirect::to('about');
        elseif (Auth::user()->hasRole('admin'))
            return Redirect::to('admin');     
        else
            return Redirect::to('home'); 
    }

    // about page of site
    public function getAbout()
    {
        return View::make('pages.about')
            ->with('title', Lang::get('home.about'));            
    }

    // a test page of site
    public function getDemo()
    {
        $hosts = Host::all();
        Host::refreshStatus($hosts);
    }
}
