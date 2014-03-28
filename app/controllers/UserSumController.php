<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 19:36:05
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 11:21:44
*/

class UserSumController extends HomeController
{
    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['home']['class'] = 'active';
        View::share('topNav', $this->topNav);
    }

    public function getSum(){
        $user_id = Auth::user()->id;
        $site = DB::table('hosts')->where('user_id',$user_id)->count();
        $log = DB::table('hosts')->where('user_id',$user_id)->sum('line_count');
        $attack = DB::table('hosts')->where('user_id',$user_id)->sum('attack_count');
        $impact = DB::table('hosts')->where('user_id',$user_id)->sum('impact_count');
        return View::make('user.sum.sum')
            ->with('title', Lang::get('home.home'))
            ->with('site', $site ? $site : 0)
            ->with('log', $log ? $log : 0)
            ->with('attack', $attack ? $attack : 0)
            ->with('impact', $impact ? $impack : 0);
    }
}