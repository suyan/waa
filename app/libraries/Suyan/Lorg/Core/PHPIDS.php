<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-23 19:48:39
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-23 22:53:42
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
        $this->phpids->config['General']['filter_path'] = $this->phpidsFilterPath;
        $this->phpids->config['General']['filter_type'] = 'json';        
        $this->phpids->config['General']['tmp_path'] = $this->phpidsTmpPath;
        $this->phpids->config['General']['scan_keys'] = false;        
        $this->phpids->config['Caching']['caching'] = 'none';
    }

    # function: pipe request through PHPIDS-filter
    function detectionPhpids($request, $threshold){
        try{
            $ids = new IDS\Monitor($this->phpids);
            $result_phpids = $ids->run($request);
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
            $ids = new IDS\Monitor($this->phpids);
            $converter = new IDS\Converter();
            $str = $converter->runAll($str, $ids);
        }catch (Exception $e){
            $this->main->log($e->getMessage());
            return false;
        }
    }

    
}