<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-18 14:05:30
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 17:13:21
*/
namespace Suyan\Lorg;
use GeoIp2;

class Detect{
    // 检查模式
    public $detectMode = array(
        'chars', //特殊符号
        'phpids', //PHPIDS
        'mcshmm', //机器学习
        'dnsbl',  //DNSBL
        'geoip',  //地理位置
        'length'  //返回长度
    );

    // client 
    public $allowedClientIdent = array('host', 'session', 'user', 'logname', 'all');
    public $clientIdent = 'all';

    // session
    public $sessionIdentifiers = 
        array('SID', 'SESSID', 'PHPSESSID', 'JSESSIONID', 'ASP.NET_SessionId');

    public $dataset = array();
    public $clients = array();

    // log
    public $lineCount = 0;
    public $lineIndex = 0;
    public $requestCount = 0;
    public $attackCount = 0;
    public $progress = -1;
    public $pathes = array();

    public $threshold = 10;

    // Log
    public $allowedInputTypes = array(
      'common'     => '%h %l %u %t \"%r\" %>s %b',
      'combined'   => '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"',
      'vhost'      => '%v %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"',
      'logio'      => '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\ %I %O"',
      'cookie'     => '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" \"%{Cookie}i\"'
    );
    public $inputType = '';
    public $regex = '';

    //time
    public $startTime;
    public $endTime;

    protected $main = null;


    public function __construct($main, $opts = array()){

        // 初始化main类，决定如何获得和存取日志信息
        $this->main = $main;
        // 检测日志格式
        if(!$this->inputType = $this->detectLogFormat())
            $this->main->logExit('日志文件格式无法识别');

        foreach($opts as $key => $value){
            $this->$key = $value;
        }

    }

    // function: try to retrieve client's identity
    function clientIdentification($data, $remote_host){
        $session = ''; 
        $user = ''; 
        $logname = '';

        // try to retrieve session id from cookie
        if (array_key_exists('Cookie', $data)){
            $entries = preg_split("/;(\ )*/", $data['Cookie']);
            foreach($entries as $entry){
                $cookie = explode('=', $entry);
                if (isset($cookie[1]) and 
                    (preg_match("/^" . implode('|', $this->sessionIdentifiers) . 
                        "$/i", $cookie[0])))
                    $session = "'" . $cookie[1] . "'";
            }
        }

        # try to retrieve session id from url query
        if (empty($session)){
            $query = explode(" ", parse_url($data['Request'], PHP_URL_QUERY));
            parse_str($query[0], $query_parsed);
            foreach ($query_parsed as $parameter => $value)
                if (preg_match("/^" . implode('|', $this->sessionIdentifiers) . "$/i", $parameter))
                    $session = $value;
        }

        # try to retrieve username (%u taken from auth)
        if (array_key_exists('Remote-User', $data) and ($data['Remote-User'] != '-'))
            $user = $data['Remote-User'];

        # try to retrieve logname (%l taken from identd)
        if (array_key_exists('Remote-Logname', $data) and ($data['Remote-Logname'] != '-'))
            $logname = $data['Remote-Logname'];

        # set ident to address, session, user, logname or all
        switch ($this->clientIdent){
            case 'host':
                $ident = $remote_host;
                break;
            case 'session':
                $ident = empty($session) ? $remote_host : $session;
                break;
            case 'user':
                $ident = empty($user) ? $remote_host : $user;
                break;
            case 'logname':
                $ident = empty($logname) ? $remote_host : $logname;
                break;
            case 'all':
                $ident = $remote_host;
                $ident .= !empty($session) ? " {" . $session . "}" : '';
                $ident .= !empty($user) ? " (" . $user . ")" : '';
                $ident .= !empty($logname) ? " [" . $logname . "]" : '';
                break;
        }

        return $ident;
    }


    function afterFirstLoop(){
        // 获得随机数据进行地理检测
        if (in_array('geoip', $this->detectMode)){
            $this->main->log('- 预处理地理信息');
            $sample_count = isset($this->dataset['geolocation']) ? 
                count($this->dataset['geolocation']) : 0;
            $sample_count = ($sample_count >= $this->main->geoip->lofMaxLearn) ? 
                $this->main->geoip->lofMaxLearn : $sample_count;

            if ($sample_count >= $this->main->geoip->lofMinLearn){
                $this->dataset['geolocation'] = 
                    Core\Helper::arrayRandMulti($this->dataset['geolocation'], $sample_count);
            }else{
                unset($this->detectMode[array_search('geoip', $this->detectMode)]); 
                // 地理信息不够
                $this->dataset['geolocation'] = null;
                $this->main->log('  - 地理信息不够');
                if (count($this->detectMode) == 0)
                    $this->main->logExit('  - 地理信息不够，无法检测');
            }
        }

        # 选择随机数据进行字节量化
        if (in_array('bytes', $this->main->quantify->quantifyType) and isset($this->dataset['query'])){
            $this->main->log('- 预处理字节信息');
            foreach ($this->dataset['query'] as $key => &$path){
                // calculate number of bytes samples to choose
                $sample_count = isset($path['bytes']) ? count($path['bytes']) : 0;
                $sample_count = ($sample_count >= $this->main->geoip->lofMinLearn) ? 
                    $this->main->geoip->lofMinLearn : $sample_count;

                if ($sample_count >= $this->main->geoip->lofMinLearn){
                    $path['bytes'] = Core\Helper::arrayRandMulti($path['bytes'], $sample_count);
                }else{
                    // 信息不够
                    $path['bytes'] = null;
                }
            }
        }

        # 最终的篡改检测
        if ($this->main->tamper->tamperTest){
            $this->main->log('- 预处理篡改检测信息');
            $this->main->tamper->preTamperTest();
        }

        # final calculation for variance/mean of request
        if (in_array('chars', $this->detectMode)){
            $this->main->anomaly->avgSubst = round($this->main->anomaly->avgSubst, 2);
            $this->main->anomaly->varSubst = round(($this->main->anomaly->indexSubst > 1) ? 
                $this->main->anomaly->varSubst / ($this->main->anomaly->indexSubst-1) : $this->main->anomaly->varSubst, 2); 
        }

        # final calculation for variance/mean of request
        if (in_array('length', $this->detectMode)){
            $this->main->anomaly->avgRequest = round($this->main->anomaly->avgRequest, 2);
            $this->main->anomaly->varRequest = round(($this->main->anomaly->indexRequest > 1) ? 
                $this->main->anomaly->varRequest / ($this->main->anomaly->indexRequest-1) : $this->main->anomaly->varRequest, 2);
        }

        # train mcshmm data for anomaly detection with hidden markov models
        if (in_array('mcshmm', $this->detectMode))
            $this->main->mcshmm->listOfEnsembles = $this->main->mcshmm->trainingMcshmm($this->dataset);
    }

    function doAggregate($data, $vector, $path, $request){
        if (array_key_exists('Remote-Host', $data)){
            $remote_host = $data['Remote-Host'];
            if (in_array('geoip', $this->detectMode))
                $ipaddr = $this->main->helper->hostnameToIpaddr($data['Remote-Host']);
        }else
            $remote_host = ($this->Detect->clientIdent == 'host') 
                ? "client_".$this->lineCount : 'unknown_host';

        // try to retrieve client's identity
        $client = $this->clientIdentification($data, $remote_host);

        if (isset($data['Final-Status']) ? preg_match("/^(2|3)[0-9]+$/", $data['Final-Status']) : true){
            if (in_array('chars', $this->detectMode)) 
                $this->main->anomaly->aggregateChars($data, $path, $vector, $client, $request, $this->dataset);

            if (in_array('length', $this->detectMode)) 
                $this->main->anomaly->aggregateLength($data, $path, $vector, $client, $request, $this->dataset);

            if (in_array('mcshmm', $this->detectMode)) 
                $this->main->mcshmm->aggregateMcshmm($path, $request, $client, $this->dataset);

            if (in_array('geoip', $this->detectMode)) 
                $this->main->geoip->aggregateGeoip($ipaddr, $this->dataset);

            if (in_array('bytes', $this->main->quantify->quantifyType)) 
                $this->main->quantify->aggregateBytes($data, $path, $vector, $client, $this->dataset);
        }
    }

    function doDetection($date, $data, $request, $path){
        if (array_filter($request)){
            $this->requestCount++;
            // reset results and tags
            $result = array(); 
            $this->main->phpids->tags = null;

            if (in_array('chars', $this->detectMode))
                $result['Chars'] = $this->main->anomaly->detectionChars($path, $request, $this->dataset);

            if (in_array('length', $this->detectMode))
                $result['Length'] = $this->main->anomaly->detectionLength($path, $request, $this->dataset);
            
            if (in_array('phpids', $this->detectMode))
                $result['PHPIDS'] = $this->main->phpids->detectionPhpids($request, $this->threshold);
            
            if (in_array('mcshmm', $this->detectMode))
                $result['MCSHMM'] = $this->main->mcshmm->detectionMcshmm($path, $request);

            $result_sum = array_sum($result);

            if (($result_sum >= $this->threshold) or in_array('geoip', $this->detectMode) or in_array('dnsbl', $this->detectMode)){
                $this->main->geoip->geoipData = null; 
                $this->main->dnsbl->dnsblData = null;

                if (array_key_exists('Remote-Host', $data)){
                    $remote_host = $data['Remote-Host'];

                    if ($this->main->geoip->geoipLookup or $this->main->dnsbl->dnsblLookup){
                        $ipaddr = $this->main->helper->hostnameToIpaddr($remote_host);
                    }

                    if ($this->main->geoip->geoipLookup)
                        $this->main->geoip->geoipData = $this->main->geoip->geoTargeting($ipaddr);

                    if ($this->main->dnsbl->dnsblLookup)
                        $this->main->dnsbl->dnsblData = $this->main->dnsbl->ipaddrToDnsbl($ipaddr);

                    if (in_array('geoip', $this->detectMode))
                        $result['GEOIP'] = $this->main->geoip->detectionGeoip($ipaddr, $this->dataset);
                    
                    if (in_array('dnsbl', $this->detectMode))
                        $result['DNSBL'] = $this->main->dnsbl->detectionDnsbl($ipaddr, $this->threshold);
                }else
                    $remote_host = 'unknown_host';
            }

            $result_sum = array_sum($result);
            if ($result_sum >= $this->threshold){
                $this->attackCount++;

                $client = $this->clientIdentification($data, $remote_host);

                if (!empty($this->main->quantify->quantifyType))
                    $success = $this->main->quantify->attackQuantification($request, $data, $path, $client, $dataset);

                if (!isset($this->clients[$client])){
                    $this->clients[$client] = new Core\Client($client);
                }

                # create action, containing data + result/tags
                $this->actions[$client][md5(serialize($data))] = new Core\Action($date, $data, $path, $result_sum, $this->main->phpids->tags, $success, $remote_host, $this->main->geoip->geoipData, $this->main->dnsbl->dnsblData);

                $this->clients[$client]->result += $result_sum;

                if (isset($success) and ($success != '-')){
                    $this->clients[$client]->severity++;
                    if (!in_array($success, $this->clients[$client]->quantification))
                        $this->clients[$client]->quantification[] = $success;
                }
            }
        }
    }

    function doSummarize($data, $request, $path){
        if (isset($request)){
            $this->main->geoip->geoipData = null;
            $this->main->dnsbl->dnsblData = null;
            if (array_key_exists('Remote-Host', $data)){
                $remote_host = $data['Remote-Host'];
                $client = $this->clientIdentification($data, $remote_host);

                if (isset($this->clients[$client])){

                    if ($this->main->geoip->geoipLookup or $this->main->dnsbl->dnsblLookup)
                        $ipaddr = $this->main->helper->hostnameToIpaddr($remote_host);

                    if ($this->main->geoip->geoipLookup)
                        $this->main->geoip->geoipData = $this->main->geoip->geoTargeting($ipaddr);

                    if ($this->main->dnsbl->dnsblLookup)
                        $this->main->dnsbl->dnsblData = $this->main->dnsbl->ipaddrToDnsbl($ipaddr);

                    if ($this->main->helper->dnsLookup){
                        $remote_host = $this->main->helper->ipaddrToHostname($remote_host);
                        $this->clients[$client]->name = 
                            $this->clientIdentification($data, $remote_host);
                    }

                    if (array_key_exists('Date', $data))
                        $date = $data['Date'] = date("r", Core\Helper::apachedateToTimestamp($data['Date']));

                    $md5_key = md5(serialize($data));

                    if (array_key_exists($md5_key, $this->actions[$client])){
                        $action = $this->actions[$client][$md5_key];
                        isset($this->pathes[$path]) ? 
                            $this->pathes[$path]++ : $this->pathes[$path] = 1;
                    }else{
                        $action = new Core\Action($date, $data, $path, 0, array('none'), '-', $remote_host, $this->main->geoip->geoipData, $this->main->dnsbl->dnsblData);
                        $this->clients[$client]->harmless_requests++;
                    }

                    $this->clients[$client]->add_action($action);
                }
            }
            $this->main->quantify->sessionClassification($this->clients, $this->requestCount);
        }
    }

    function httpdataToVector($data){
        if (array_key_exists('Request', $data)){
            if (preg_match("/^(\S+) (.*?) HTTP\/[0-9]\.[0-9]\z/", $data['Request'], $match)){
                $url_query = parse_url($match[2], PHP_URL_QUERY);
                if ((!$url_query) and (preg_match('/[^\w!\/~#+-.]/', $match[2])))
                    $url_query = $match[2];
                parse_str($url_query, $parameters);
                $path = parse_url($match[2], PHP_URL_PATH);
                $argnames = array_keys($parameters);
            }else{
                parse_str($data['Request'], $parameters);
                $path = null; $argnames = null;
            }

            foreach ($parameters as $key => &$val)
                if (is_array($val))
                    $val = implode_recursive('', $val);

            $cookie = (array_key_exists('Cookie', $data) and ($data['Cookie'] != '-')) ? $data['Cookie'] : '';
            $agent = (array_key_exists('User-Agent', $data) and ($data['User-Agent'] != '-')) ? $data['User-Agent'] : '';

            $request = null;
            
            if (!$this->main->quantify->onlyCheckWebapps or (preg_match("/.*(" . implode('|', $this->main->quantify->webAppExtensions) . ")$/", $path)))
                $request['query'] = !empty($parameters) ? $parameters : null;
            
            if ($this->main->mcshmm->addVector)
                foreach ($this->main->mcshmm->addVector as $vector)
                    $request[$vector] = (!empty($$vector)) ? $$vector : null;

            if ($this->main->phpids->usePhpidsConverter and isset($request)) 
                if (in_array('chars', $this->detectMode) or in_array('mcshmm', $this->detectMode))
                    array_walk_recursive($request, array($this->main->phpids,'convertUsingPhpids'));
            
            return array($request, $path);
        }
        else
            return null;
    }

    // 检测日志文件的类型
    function detectLogFormat(){
        $this->main->resetInput();
        for ($line_index = 0; $line_index < 10; $line_index++){
            $line = $this->main->getLine();
            $line = trim($line);
            foreach($this->allowedInputTypes as $key => $format){
                // regex = {$regex_fields, $regex_string, $num_fields}
                $regex = Core\Helper::formatToRegex($format);
                $data = Core\Helper::loglineToHttpdata($line, $regex);
                if ($data){
                    $this->regex = $regex;
                    return $key;
                }
            }
        }
        return false;
    }

    public function  preProcessing(){
        $this->main->log('开始预处理');
        $this->main->resetInput();
        // 完成第一个循环，获得行数，和基本的dataset
        while($line = $this->main->getLine()){
            trim($line);
            $data = Core\Helper::loglineToHttpdata($line,$this->regex);
            $this->lineCount++;
            if(isset($data)){
                $vector = $this->httpdataToVector($data);
                $request = $vector[0];
                $path = $vector[1];
                $this->main->tamper->tamperTest($data);
                $this->doAggregate($data, $vector, $path, $request);
            }
        }
        $this->main->log('- 文件总行数: '.$this->lineCount);
        $this->afterFirstLoop();
    }

    public function mainProcessing(){
        $this->main->log('开始正式检测');
        $this->main->resetInput();
        clearstatcache();
        while($line = $this->main->getLine()){
            trim($line);
            $data = Core\Helper::loglineToHttpdata($line,$this->regex);
            $this->lineIndex++;
            if(isset($data)){
                $vector = $this->httpdataToVector($data);
                $request = $vector[0];
                $path = $vector[1];

                //篡改检测
                if (array_key_exists('Date', $data))
                    $date = $data['Date'] = date("r", Core\Helper::apachedateToTimestamp($data['Date']));
                if (isset($this->main->tamper->lastDate))
                    $this->main->tamper->tampterTestGrubbs($date);

                $this->main->tamper->lastDate = $date;
                $this->doDetection($date, $data, $request, $path);
            }
        }
    }

    public function postProcessing(){
        $this->main->log('信息汇总');
        $this->main->resetInput();
        while($line = $this->main->getLine()){
            trim($line);
            $data = Core\Helper::loglineToHttpdata($line,$this->regex);
            $this->lineIndex++;
            if(isset($data)){
                $vector = $this->httpdataToVector($data);
                $request = $vector[0];
                $path = $vector[1];
                if (isset($data)){
                    $this->doSummarize($data, $request, $path);
                }
            }
        }
    }

    public function run(){
        $this->startTime = time();
        $this->main->logProcess(0);
        $this->preProcessing();
        $this->main->logProcess(30);
        $this->mainProcessing();
        $this->main->logProcess(60);
        $this->postProcessing();
        $this->main->logProcess(100);
        $this->endTime = time();
        return true;
    }
}
?>