<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostsTable extends Migration {

    public function up()
    {
        Schema::create('hosts', function($table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('hostname');
            $table->string('domain');
            $table->string('description');
            $table->string('file_name');
            $table->string('file_md5');
            $table->integer('file_size')->unsigned()->default(0);
            $table->integer('status')->unsigned()->default(0);
            $table->integer('line_count')->unsigned()->default(0);
            $table->integer('attack_count')->unsigned()->default(0);
            $table->integer('impact_count')->unsigned()->default(0);
            $table->integer('process')->unsigned()->default(0);
            $table->string('log')->default(' ');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        $hosts = DB::table('hosts')->get();
        foreach($hosts as $host) {
            File::delete(Config::get('app.upload_dir').'/'.$host->file_name);
        }
        Schema::drop('hosts');
    }

}
