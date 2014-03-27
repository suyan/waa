<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-24 10:38:13
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 10:50:43
*/
namespace Suyan\Lorg\Core;
class Tamper
{
    public $tamperTest = false;
    public $currentDate = 0;
    public $lastDate = 0;
    public $maxDelay = 0.0;
    public $avgDelay = 0.0;
    public $varDelay = 0.0;
    public $indexDelay = 0;
    public $log;

    public function __construct($opts, $log){
        if(!isset($log)) return false;
        $this->log = $log;

        foreach($opts as $key => $value){
            $this->$key = $value;
        }
    }

    function tamperTest($data){
        if($this->tamperTest and (isset($data['Date']))){
            $this->currentDate = strtotime(date("r", Helper::apachedateToTimestamp($data['Date'])));
            if ($this->lastDate){
                $value = $this->currentDate - $this->lastDate;
                $this->maxDelay = max($value
                    , (isset($this->maxDelay) ? $this->maxDelay : 0));
                Helper::onlineVariance($this->avgDelay,
                    $this->varDelay, $this->indexDelay, $value);
                $this->lastDate = $this->currentDate;
            }
        }
    }

    function preTamperTest(){
        $this->avgDelay = round($this->avgDelay, 2);
        $this->varDelay = round(($this->indexDelay > 1) ? 
            $this->varDelay / ($this->indexDelay-1) : $this->varDelay, 2);
        unset($this->lastDate);
    }

    # function: do a naive tamper test based on inter-request time delays
    function tampterTestGrubbs($date){
        $delay = strtotime($date) - strtotime($this->lastDate);
        if (($delay == $this->maxDelay) and ($this->indexDelay > 2) and ($this->varDelay != 0)){
            $grubbs = ($delay - $this->avgDelay) / sqrt($this->varDelay);

            $g_dist = array(
                3 =>   1.1531, 4 =>   1.4625, 5 =>   1.6714, 6 =>   1.8221, 7 =>   1.9381,
                8 =>   2.0317, 9 =>   2.1096, 10 =>  2.1761, 11 =>  2.2339, 12 =>  2.2850,
                13 =>  2.3305, 14 =>  2.3717, 15 =>  2.4090, 16 =>  2.4433, 17 =>  2.4748,
                18 =>  2.5040, 19 =>  2.5312, 20 =>  2.5566, 25 =>  2.6629, 30 =>  2.7451,
                40 =>  2.8675, 50 =>  2.9570, 60 =>  3.0269, 70 =>  3.0839, 80 =>  3.1319,
                90 =>  3.1733, 100 => 3.2095, 120 => 3.2706, 140 => 3.3208, 160 => 3.3633,
                180 => 3.4001, 200 => 3.4324, 300 => 3.5525, 400 => 3.6339, 500 => 3.6952);

            foreach ($g_dist as $key => $val){
                $critical_value = $val;
                if ($key >= $this->indexDelay)
                    break;
            }

            $gmdate_string = 's \s\e\c'; // default: only seconds
            $gmdate_string = ((($delay / 60) >= 1) ? 'i \m\i\\n, ' : '') . $gmdate_string;
            $gmdate_string = ((($delay / 3600) >= 1) ? 'H \h\o\u\\r\s, ' : '') . $gmdate_string;
            $gmdate_string = ((($delay / 86400) >= 1) ? 'd \d\a\y\s, ' : '') . $gmdate_string;
            if ($grubbs > $critical_value) 
                $this->log("在".$this->lastDate."可能有篡改: 有".gmdate($gmdate_string, $delay)."无请求");
            else 
                $this->log('日志没发现篡改');
        }
    }
}