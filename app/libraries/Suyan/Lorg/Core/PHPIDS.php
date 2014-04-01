<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-23 19:48:39
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-01 10:44:01
*/
namespace Suyan\Lorg\Core;
use IDS;


class PHPIDS 
{
    public $usePhpidsConverter = true;
    public $phpidsConfigPath = ''; //必须配置
    public $phpidsFilterPath = ''; //必须配置
    public $phpidsTmpPath = ''; //必须配置
    public $phpids = null;
    public $monitor = null;
    public $converter = null;
    public $addTags = true;
    public $tags = null;
    public $tagStats = array();
    public $tagsCount = 0;
    public $log;

    public function __construct($opts, $log){
        if(!isset($log)) return false;
        $this->log = $log;
        // 检测配置
        $musts = array('phpidsConfigPath','phpidsFilterPath','phpidsTmpPath');
        foreach($musts as $must){
            if(!isset($opts[$must]))
                $this->log->logExit('缺少变量'.$must);    
        }

        foreach($opts as $key => $value){
            $this->$key = $value;
        }

        $this->phpids = IDS\Init::init();
        $this->phpids->config['General']['filter_type'] = $this->phpidsFilterType;
        $this->phpids->config['General']['filter_path'] = $this->phpidsFilterPath;
        $this->phpids->config['General']['tmp_path'] = $this->phpidsTmpPath;
        $this->phpids->config['General']['scan_keys'] = false;        
        $this->phpids->config['Caching']['caching'] = 'file';
        $this->phpids->config['Caching']['path'] = $this->phpidsTmpPath.'/filter.cache';
        $this->phpids->config['Caching']['expiration_time'] = 600;

        $this->monitor = new IDS\Monitor($this->phpids);
        $this->converter = new IDS\Converter();
    }

    # function: pipe request through PHPIDS-filter
    function detectionPhpids($request, $threshold){
        try{
            $result_phpids = $this->monitor->run($request);
        }catch (Exception $e){
            $this->log($e->getMessage());
            return false;
        }

        if ($result_phpids->isEmpty())
            $result_phpids = $this->fakePhpidsResult();
        elseif ($result_phpids->getImpact() >= $threshold){
            foreach($result_phpids->getTags() as $tag){
                if (!(isset($this->tagStats[$tag])))
                    $this->tagStats[$tag] = 1;
                else
                    $this->tagStats[$tag]++;
                $this->tagsCount++;
            }
        }
        $this->tags = $result_phpids->getTags();
        return $result_phpids->getImpact();
    }

    # function: create a fake PHPIDS result with zero impact
    function fakePhpidsResult(){
        $filter = new IDS\Filter('-1', '(.*)', 'dummy', array(0 => 'none'), 0);
        $event = new IDS\Event('dummy', 'none', array($filter));
        $result = new IDS\Report(array($event));
        return $result;
    }

    // function: normalize parts of a request, using the PHPIDS converter
    function convertUsingPhpids(&$str, $key){
        try {
            $str = $this->converter->runAll($str, $this->monitor);
        }catch (Exception $e){
            $this->main->log($e->getMessage());
            return false;
        }
    }

    
}