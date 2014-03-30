<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Indigo\Supervisor\Supervisor;
use Indigo\Supervisor\Process;
use Indigo\Supervisor\Connector;

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

        // 打开处理进程
        $connector = new Connector\InetConnector(
            Config::get('waa.supervisor_host'), 
            Config::get('waa.supervisor_port')
            );
        $connector->setCredentials(
            Config::get('waa.supervisor_name'),
            Config::get('waa.supervisor_password'));
        $supervisor = new Supervisor($connector);
        try{
            $supervisor->startAllProcesses(false);     
        } catch (Exception $e){
            Log::error($e);
        }
        
    }

    public function down()
    {
        $hosts = DB::table('hosts')->get();
        
        // 关闭处理进程
        $connector = new Connector\InetConnector(
            Config::get('waa.supervisor_host'), 
            Config::get('waa.supervisor_port')
            );
        $connector->setCredentials(
            Config::get('waa.supervisor_name'),
            Config::get('waa.supervisor_password')
            );
        $supervisor = new Supervisor($connector);
        try{
            $supervisor->stopProcessGroup('waaQueue',false);      
        } catch (Exception $e){
            Log::error($e);
        }
        
        // 清空队列
        $redis = Redis::connection();
        $redis->flushdb();
        foreach($hosts as $host) {
            File::delete(Config::get('waa.upload_dir').'/'.$host->file_name);
        }
        Schema::drop('hosts');
    }

}
