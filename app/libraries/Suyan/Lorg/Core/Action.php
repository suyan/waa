<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-20 12:05:22
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 18:27:48
*/

namespace Suyan\Lorg\Core;

class Action
{
    public $date;
    public $data;
    public $path;
    public $result;
    public $tags;
    public $quantification;
    public $remote_host;
    public $geoip_data;
    public $dnsbl_data;
    public $new_session;

    # function: construct action
    public function __construct($date, $data, $path, $result, $tags, $quantification, $remote_host, $geoip_data, $dnsbl_data)
    {
        $this->date = $date;
        $this->data = $data;
        $this->path = $path;
        $this->result = $result;
        $this->tags = $tags;
        $this->quantification = $quantification;
        $this->remote_host = $remote_host;
        $this->geoip_data = $geoip_data;
        $this->dnsbl_data = $dnsbl_data;
    }
}
?>