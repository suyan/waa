<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVectorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vectors', function($table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');
			$table->integer('host_id');
			$table->string('client');
			$table->integer('impact');
			$table->string('tags');
			$table->string('quantification');
			$table->integer('status')->unsigned();
			$table->text('request');
			$table->string('bytes');
			$table->string('remote_city');
			$table->string('remote_code');
			$table->string('location');
			$table->timestamp('date');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vectors');
	}

}
