<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 20:14:35
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 10:26:13
*/
class AdminTaskController extends AdminController
{

    public $leftNav;

    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['task']['class'] = 'active';
        View::share('topNav', $this->topNav);

        $this->leftNav = array(
            'wait' => array(
                'name' => 'admin.wait_queue',
                'url' => 'admin/task',
                'class' => ''
            ),
            'run' => array(
                'name' => 'admin.run_queue',
                'url' => 'admin/task/run',
                'class' => ''
            ),
            'done' => array(
                'name' => 'admin.done_queue',
                'url' => 'admin/task/done',
                'class' => ''
            )
        );
    }

    public function getWait(){
        $this->leftNav['wait']['class'] = 'active';
        $hosts = Host::where('status',1)->paginate(Config::get('waa.paginate'));
        return View::make('admin.task.wait')
            ->with('title', Lang::get('admin.wait_queue'))
            ->with('leftNav', $this->leftNav)
            ->with('hosts', $hosts);
    }

    public function getRun(){
        $this->leftNav['run']['class'] = 'active';
        $hosts = Host::where('status',2)->paginate(Config::get('waa.paginate'));
        return View::make('admin.task.run')
            ->with('title', Lang::get('admin.run_queue'))
            ->with('leftNav', $this->leftNav)
            ->with('hosts', $hosts);
    }

    public function getDone(){
        $this->leftNav['done']['class'] = 'active';
        $hosts = Host::where('status',3)->paginate(Config::get('waa.paginate'));
        return View::make('admin.task.done')
            ->with('title', Lang::get('admin.done_queue'))
            ->with('leftNav', $this->leftNav)
            ->with('hosts', $hosts);
    }

}