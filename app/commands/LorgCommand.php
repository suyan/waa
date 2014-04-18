<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LorgCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lorg';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Test lorg in command';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info('开始分析');
		$lorg = new Suyan\Lorg\Lorg('Laravel', 'waa');
		// 从文件中读取配置项
		$lorg->config->set('input', array(
		    'default'=>'File', 
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

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
