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
		$lorg = new Suyan\Lorg\Lorg('File', app_path().'/libraries/Suyan/Lorg/Data/config.php');
		$lorg->config->set('input', array(
		    'default'=>'File', 
		    'File'=>app_path().'/libraries/Suyan/Lorg/Data/log_test'
		    ));
		$lorg->run();
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
