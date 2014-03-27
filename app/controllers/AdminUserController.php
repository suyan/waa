<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 19:36:05
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-25 22:06:31
*/

class AdminSumController extends AdminController
{
    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['home']['class'] = 'active';
        View::share('topNav', $this->topNav);
    }

    public function getUser(){
        
    }
}