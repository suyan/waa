<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-17 22:36:34
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-30 10:32:21
*/
namespace Suyan\Lorg;
class Lorg{

    public $log;
    public $config;
    public $input;
    public $output;
    public $detect;
    public $phpids;
    public $geoip;

    public $impactCount = 0;

    /**
     * 初始化LORG类
     */
    public function __construct($opts){   
        $this->config = new Config\Config($opts);
    }

    /**
     * 初始化一些必要的类
     */
    public function initHelper(){

        // 初始化输入方式
        $this->input = new Input\Input($this->config->get('input'));

        // 初始化输出部分
        $output_opts = $this->config->get('output');
        $this->output = new Output\Output($output_opts['type']);
        $this->output->init($output_opts[$output_opts['type']]);

        $log_opts = $this->config->get('log');
        $this->log = new Log\Log($log_opts['type']);
        $this->log->init($log_opts[$log_opts['type']]);

        // 初始化需要负责检测的类
        $this->phpids = new Core\PHPIDS($this->config->get('phpids'),$this->log);
        $this->geoip = new Core\Geoip($this->config->get('geoip'),$this->log);
        $this->dnsbl = new Core\Dnsbl($this->config->get('geoip'),$this->log);
        $this->helper = new Core\Helper($this->config->get('helper'),$this->log);
        $this->tamper = new Core\Tamper($this->config->get('tamper'),$this->log);
        $this->mcshmm = new Core\Mcshmm($this->config->get('mcshmm'),$this->log);
        $this->quantify = new Core\Quantify($this->config->get('quantify'),$this->log,$this->geoip);
        $this->anomaly = new Core\Anomaly($this->config->get('anomaly'), $this->log);
    }

    /**
     * 由run函数来操控如何去检测这个系统
     */
    function run(){
        $this->initHelper();
        // 开始运行
        $opts = $this->config->get('detect');
        
        // 初始化检测类，并且把自身传递
        $this->detect = new Detect($this, $opts);
        $this->detect->run();
        

        if ($this->detect->summarize)
            $this->writeVectors();

        $this->writeSummarize();     
    }

    function resetInput(){
        $this->input->resetInput();
    }

    function getLine(){
        return $this->input->getLine();
    }

    function log($msg){
        $this->log->log($msg);
    }

    function logExit($msg){
        $this->log->logExit($msg);
    }

    function logProcess($process){
        $this->log->logProcess($process);
    }
    
    function writeVectors(){
        foreach($this->detect->clients as $client){
            foreach($client->actions as $action){
                $this->output->writeVector(array(
                    'client' => $action->data['Remote-Host'],
                    'impact' => $action->result,
                    'tags' => implode(',', $action->tags),
                    'quantification' => $action->new_session ? $action->new_session : 'none',
                    'status' => $action->data['Final-Status'],
                    'request' => $action->data['Request'],
                    'bytes' => $action->data['Bytes-Sent'],
                    'remote_city' => isset($action->geoip_data[0]) ? $action->geoip_data[0] : 'unknown',
                    'remote_code' => isset($action->geoip_data[2]) ? $action->geoip_data[2] : 'unknown',
                    'location' => isset($action->geoip_data[1]) ? $action->geoip_data[1]: 'unknown',
                    'date' => date('Y-m-d H:i:s',strtotime($action->data['Date']))
                    ));
                $this->impactCount += $action->result;
            }
        }
    }

    function writeVector($action){
        $this->output->writeVector(array(
            'client' => $action->data['Remote-Host'],
            'impact' => $action->result,
            'tags' => implode(',', $action->tags),
            'quantification' => $action->new_session ? $action->new_session : 'none',
            'status' => $action->data['Final-Status'],
            'request' => $action->data['Request'],
            'bytes' => $action->data['Bytes-Sent'],
            'remote_city' => isset($action->geoip_data[0]) ? $action->geoip_data[0] : 'unknown',
            'remote_code' => isset($action->geoip_data[2]) ? $action->geoip_data[2] : 'unknown',
            'location' => isset($action->geoip_data[1]) ? $action->geoip_data[1]: 'unknown',
            'date' => date('Y-m-d H:i:s',strtotime($action->data['Date']))
            ));
        $this->impactCount += $action->result;
    }

    function writeSummarize(){
        $this->output->writeSummarize(array(
            'tag_stats' => $this->phpids->tagStats,
            'attack_count' => $this->detect->attackCount,
            'tags_count' => $this->phpids->tagsCount,
            'line_count' => $this->detect->lineCount,
            'impact_count' => $this->impactCount,
            'start_time' => $this->detect->startTime,
            'end_time' => $this->detect->endTime,
            ));
    }
}
?>
