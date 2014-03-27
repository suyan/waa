<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 19:36:05
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-25 20:30:09
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
            ->with('site', $site)
            ->with('log', $log)
            ->with('attack', $attack)
            ->with('impact', $impact);
    }
}