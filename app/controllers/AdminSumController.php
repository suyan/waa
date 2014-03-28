<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 19:36:05
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 11:21:23
*/

class AdminSumController extends AdminController
{
    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['home']['class'] = 'active';
        View::share('topNav', $this->topNav);
    }

    public function getSum(){
        $site = DB::table('hosts')->remember(10)->count();
        $log = DB::table('hosts')->remember(10)->sum('line_count');
        $attack = DB::table('hosts')->remember(10)->sum('attack_count');
        $impact = DB::table('hosts')->remember(10)->sum('impact_count');
        return View::make('admin.sum.sum')
            ->with('title', Lang::get('admin.home'))
            ->with('site', $site ? $site : 0)
            ->with('log', $log ? $log : 0)
            ->with('attack', $attack ? $attack : 0)
            ->with('impact', $impact ? $impack : 0);
    }
}