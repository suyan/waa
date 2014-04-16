<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 20:14:35
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-16 19:18:26
*/
use Indigo\Supervisor\Supervisor;
use Indigo\Supervisor\Process;
use Indigo\Supervisor\Connector;

class AdminTaskController extends AdminController
{

    public $leftNav;

    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['task']['class'] = 'active';
        View::share('topNav', $this->topNav);

        $this->leftNav = array(
            'process' => array(
                'name' => 'admin.process.process',
                'url' => 'admin/task/process',
                'class' => ''),
            'wait' => array(
                'name' => 'admin.wait_queue',
                'url' => 'admin/task/wait',
                'class' => ''),
            'run' => array(
                'name' => 'admin.run_queue',
                'url' => 'admin/task/run',
                'class' => ''),
            'done' => array(
                'name' => 'admin.done_queue',
                'url' => 'admin/task/done',
                'class' => '')
        );
    }

    public function getWait(){
        $this->leftNav['wait']['class'] = 'active';
        $hosts = Host::where('status',1)->paginate(Config::get('waa.paginate'));
        return View::make('admin.task.waitlist')
            ->with('title', Lang::get('admin.wait_queue'))
            ->with('leftNav', $this->leftNav)
            ->with('hosts', $hosts);
    }

    public function getRun(){
        $this->leftNav['run']['class'] = 'active';
        $hosts = Host::where('status',2)->paginate(Config::get('waa.paginate'));
        return View::make('admin.task.runlist')
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

    public function getProcess(){
        $this->leftNav['process']['class'] = 'active';
        $connector = new Connector\InetConnector(
            Config::get('waa.supervisor.host'), 
            Config::get('waa.supervisor.port')
            );
        $connector->setCredentials(
            Config::get('waa.supervisor.name'),
            Config::get('waa.supervisor.password'));
        try{
            $supervisor = new Supervisor($connector);
            $processes = $supervisor->getAllProcessInfo();    
        } catch (Exception $e) {
            $processes = array();
            $error = Lang::get('admin.process.supervisor_error');
        }
        
        return View::make('admin.task.process')
            ->with('title', Lang::get('admin.process.process'))
            ->with('leftNav', $this->leftNav)
            ->with('error', isset($error) ? $error : null)
            ->with('processes', $processes);   
    }

    public function getProcessStart($process){
        $this->leftNav['process']['class'] = 'active';

        return View::make('admin.task.startprocess')
            ->with('title', Lang::get('admin.process.start'))
            ->with('leftNav', $this->leftNav)
            ->with('process', $process);
    }

    public function postProcessStart($process){
        $process_name = Config::get('waa.supervisor.group').':'.$process;

        $connector = new Connector\InetConnector(
            Config::get('waa.supervisor.host'), 
            Config::get('waa.supervisor.port')
            );
        $connector->setCredentials(
            Config::get('waa.supervisor.name'),
            Config::get('waa.supervisor.password'));
        $supervisor = new Supervisor($connector);
        try{
            $supervisor->startProcess($process_name, false);    
        } catch(Exception $e) {}
        

        return Redirect::to('admin/task/process')
            ->with('info', Lang::get('admin.process.start_ok'));
    }

    public function getProcessStop($process){
        $this->leftNav['process']['class'] = 'active';

        return View::make('admin.task.stopprocess')
            ->with('title', Lang::get('admin.process.stop'))
            ->with('leftNav', $this->leftNav)
            ->with('process', $process);
    }

    public function postProcessStop($process){
        $process_name = Config::get('waa.supervisor.group').':'.$process;

        $connector = new Connector\InetConnector(
            Config::get('waa.supervisor.host'), 
            Config::get('waa.supervisor.port')
            );
        $connector->setCredentials(
            Config::get('waa.supervisor.name'),
            Config::get('waa.supervisor.password'));
        $supervisor = new Supervisor($connector);

        try{
            $supervisor->stopProcess($process_name, false);
        } catch(Exception $e) {}
        
        return Redirect::to('admin/task/process')
            ->with('info', Lang::get('admin.process.stop_ok'));
    }

}