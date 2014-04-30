<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LorgCommand extends Command {
    protected $name = 'lorg';
    protected $description = 'Test lorg in command';
    public function __construct()
    {
        parent::__construct();
    }
    public function fire()
    {
        $this->info('开始分析');
        // 以Laravel格式获得配置文件，配置文件在waa中
        $lorg = new Suyan\Lorg\Lorg('Laravel', 'waa');

        // 输入方式，从文件输入
        $lorg->config->set('input', array(
            'type'=>'File', 
            'File'=>app_path().'/libraries/Suyan/Lorg/Data/log_test'
            // 'File' => '/Users/yansu/Downloads/access_log'
            ));
        
        include_once '/usr/local/Cellar/php55-xhprof/254eb24/xhprof_lib/utils/xhprof_lib.php';
        include_once '/usr/local/Cellar/php55-xhprof/254eb24/xhprof_lib/utils/xhprof_runs.php'; 
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
         
        $lorg->run();
         
        $xhprof_data = xhprof_disable();
        $profiler_namespace="hello";
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
    }

    protected function getArguments()
    {
        return array(
            // array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    protected function getOptions()
    {
        return array(
            // array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
