<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-24 13:03:25
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 17:03:41
*/

namespace Suyan\Lorg\Core;

class Anomaly
{

    public $varMinLearn = 10;
    public $varCiticalVal = 10;
    public $avgRequest = 0.0;
    public $avgSubst = 0.0;
    public $varRequest = 0.0;
    public $varSubst = 0.0; 
    public $indexRequest = 0;
    public $indexSubst = 0; 

    public function __construct($opts, $log){
        if(!isset($log)) return false;
        $this->log = $log;

        foreach($opts as $key => $value){
            $this->$key = $value;
        }
    }

    # function: add information needed for chars detection to dataset
    function aggregateChars($data, $path, $vector, $client, $request, &$dataset){
        if (!isset($vector[0]['query']))
            return null;

        if (isset($path)){
            $chars = &$dataset['query'][$path]['chars'];

            if (isset($chars['clients'][$client]))
                return null;

            $value = strlen(Helper::removeAlphanumeric(Helper::implodeRecursive('', $request)));
            // global online variance and mean calculation of substituted request's length
            Helper::onlineVariance($this->avgSubst, $this->varSubst, $this->indexSubst, $value);
            // local online variance and mean calculation of substituted request's length
            Helper::onlineVariance($chars['avg'], $chars['var'], $chars['idx'], $value);
            // mark current client as 'has contributed'
            $chars['clients'][$client] = true;
        }
    }

    # function: add information needed for length detection to dataset
    function aggregateLength($data, $path, $vector, $client, $request, &$dataset){
        if (!isset($vector[0]['query']))
            return null;

        if (isset($path)){
            $length = &$dataset['query'][$path]['length'];
            if (isset($length['clients'][$client]))
                return null;

            $value = strlen(Helper::implodeRecursive('', $request));

            Helper::onlineVariance($this->avgSubst, $this->varSubst, $this->indexSubst, $value);
            Helper::onlineVariance($length['avg'], $length['var'], $length['idx'], $value);

            $length['clients'][$client] = true;
        }
    }

    # function: simple, fast (and inaccurate) statistical anomaly detection
    function detectionChars($path, $request, $dataset){
        $result_chars = 0;

        if (isset($dataset['query'][$path]['chars']))
            $chars = $dataset['query'][$path]['chars'];

        $value = strlen(Helper::removeAlphanumeric(Helper::implodeRecursive('', $request)));

        if (isset($chars['clients']) and ($chars['clients'] >= $this->varMinLearn)){
            if ($value > $chars['avg'] and ($value > 2))
                $result_chars = round(log($value, 1.25) + 1);
        }else{
            if ($value > $this->avgSubst and ($this->avgSubst > 0) and ($value > 2))
                $result_chars = round(log($value/$this->avgSubst));
        }

        return($result_chars);
    }

    # function: simple, fast (and inaccurate) statistical anomaly detection
    function detectionLength($path, $request, $dataset){
        $result_length = 0;

        if (isset($dataset['query'][$path]['length']))
            $length = $dataset['query'][$path]['length'];

        $value = strlen(Helper::implodeRecursive('', $request));

        if (isset($length['clients']) and ($length['clients'] >= $this->varMinLearn)){
            if (($value > $length['avg']) and ($length['var'] != 0))
                $result_length = round(
                    log(1 / ($length['var'] / pow($value - $length['avg'], 2)), 1.15) + 1);
        }else{
            if (($value > $this->avgRequest)  and ($this->varRequest != 0))
                $result_length = round(
                    log(1 / ($this->varRequest / pow($value - $this->avgRequest, 2)), 1.15)+ 1);
        }

        return($result_length);
    }

    public function calculateChars(){
        $this->avgSubst = round($this->avgSubst, 2);
        $this->varSubst = round(($this->indexSubst > 1) ? 
            $this->varSubst / ($this->indexSubst-1) : $this->varSubst, 2);
    }

    public function calculateLength(){
        $this->avgRequest = round($this->avgRequest, 2);
        $this->varRequest = round(($this->indexRequest > 1) ? 
            $this->varRequest / ($this->indexRequest-1) : $this->varRequest, 2);
    }

}