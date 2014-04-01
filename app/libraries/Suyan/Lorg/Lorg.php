<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-17 22:36:34
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-31 17:54:27
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

    public function __construct($config = 'File', $source = 'Suyan/Lorg/Data/config.php'){   
        $this->config = new Config\Config($config);
        $this->config->init($source);
    }

    public function initHelper(){
        $input_opts = $this->config->get('input');
        $this->input = new Input\Input($input_opts['default']);
        $this->input->init($input_opts[$input_opts['default']]);

        $output_opts = $this->config->get('output');
        $this->output = new Output\Output($output_opts['default']);
        $this->output->init($output_opts[$output_opts['default']]);

        $log_opts = $this->config->get('log');
        $this->log = new Log\Log($log_opts['default']);
        $this->log->init($log_opts[$log_opts['default']]);

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

    function run(){
        $this->initHelper();
        // 开始运行
        $opts = $this->config->get('detect');
        
        // 初始化检测类，并且把自身传递
        $this->detect = new Detect($this, $opts);
        $this->detect->run();
        
        $this->writeVectors();
        // var_dump($this->detect->clients);
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
                    'remote_city' => $action->geoip_data[0],
                    'remote_code' => $action->geoip_data[2],
                    'location' => $action->geoip_data[1],
                    'date' => date('Y-m-d H:i:s',strtotime($action->data['Date']))
                    ));
                $this->impactCount += $action->result;
            }
        }
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
