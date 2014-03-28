<?php

/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-23 19:48:39
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 16:52:34
*/

namespace Suyan\Lorg\Core;
use GeoIp2;


class Geoip
{
    // geoip
    public $geoipLookup = true;
    public $geoipFile = ''; // 必须配置
    public $geoip = null;
    public $geoipCache = null;
    public $geoipData = null;
    public $lofCache = null;
    public $lofMinLearn = 40;
    public $lofMaxLearn = 150;
    public $lofMinptsLb = 10;
    public $lofMinptsUb = 20;
    public $log;

    public function __construct($opts, $log){
        if(!isset($log)) return false;
        $this->log = $log;

        if(!isset($opts['geoipFile']))
            $this->log->logExit('缺少变量 geoipFile');   

        foreach($opts as $key => $value){
            $this->$key = $value;
        }

        if(!file_exists($this->geoipFile))
            $this->log->logExit('geoipFile 不存在');               
            
        $this->geoip = new GeoIp2\Database\Reader($this->geoipFile);
    }

    # function: try to retrieve client's geo-infos
    function geoTargeting($ipaddr){
        if (isset($this->geoipCache[$ipaddr]))
            return $this->geoipCache[$ipaddr];
        else
            if (isset($this->geoip)){
                // set defaults
                $remote_city = '-'; 
                $remote_location = '-'; 
                $country_code = null;

                // do the geoiplookup
                $geoip_record = $this->geoip->city($ipaddr);

                // build the geoip-info
                if ($geoip_record->country->name)
                    $remote_city = $geoip_record->country->name;
                if ($geoip_record->city->name)
                    $remote_city = $remote_city . ', ' . $geoip_record->city->name;
                if ($geoip_record->location->latitude && $geoip_record->location->longitude)
                    $remote_location = number_format($geoip_record->location->latitude, 4) 
                        . ',' . number_format($geoip_record->location->longitude, 4);
                if ($geoip_record->country->isoCode)
                    $country_code = $geoip_record->country->isoCode;

                // geoip-info contains remote city/county and geocordinates
                $this->geoipData = array($remote_city, $remote_location, $country_code);

                // add entry to geoip cache
                $this->geoipCache[$ipaddr] = $this->geoipData;
                return $this->geoipData;
            }else 
                return null;
    }

    // function: add information needed for geoip detection to dataset
    function aggregateGeoip($ipaddr, &$dataset){
        if (!isset($dataset['geolocation']) or 
            (count($dataset['geolocation']) < $this->lofMaxLearn * 10)){
            $this->geoipData = $this->geoTargeting($ipaddr);
            $geolocation = explode(',', $this->geoipData[1]);
            if ($geolocation[0] != '-')
                if (!isset($dataset['geolocation'][$ipaddr])){
                    $dataset['geolocation'][$ipaddr] = array(
                        $geolocation[0] + 0.01 * rand(0, 99), 
                        $geolocation[1] + 0.01 * rand(0, 99));
                }
        }
    }

    # function: anomaly detection based on GeoIP information
    function detectionGeoip($ipaddr, $dataset){
        $result_geoip = 0;
        $geolocation = explode(',', $this->geoipData[1]);

        if (isset($this->lofCache['lof'][$this->geoipData[1]]))
            $result_geoip = $this->lofCache['lof'][$this->geoipData[1]];
        else{
            if ($geolocation[0] != '-'){
                $lof_data = $dataset['geolocation'];
                for ($lof_neighbors=$this->lofMinptsLb; $lof_neighbors <= $this->lofMinptsUb; $lof_neighbors++){
                    $lof = new LOF($lof_data, $lof_neighbors, $this->lofCache);
                    $lof_values[$lof_neighbors] = $lof->run($ipaddr, $geolocation);
                }
                $result_geoip = round(max($lof_values));

                $this->lofCache['lof'][$this->geoipData[1]] = $result_geoip;
            }
        }

        return($result_geoip);
    }
}