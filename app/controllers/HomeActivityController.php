<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-26 10:50:02
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-16 21:32:08
*/
class HomeActivityController extends HomeController
{
    public $leftNav;
    
    public function __construct()
    {
        parent::__construct();
        
        //set top nav
        $this->topNav['activity']['class'] = 'active';
        View::share('topNav', $this->topNav);
        //set left nav
        $this->leftNav = array(
            'activity' => array(
                'name' => 'home.all_activity',
                'url' => 'activity',
                'class' => ''
            ),
        );
    }

    public function getActivity()
    {
        $this->leftNav['activity']['class'] = 'active';
        
        $activities = Activity::where('user_id', Auth::user()->id)->paginate(20);

        return View::make('home.activity.activity')
            ->with('leftNav', $this->leftNav)
            ->with('title', Lang::get('host.activity'))
            ->with('activities', $activities);
    }
}