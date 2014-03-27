<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-24 10:01:32
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 10:09:32
*/

namespace Suyan\Lorg\Core;

class Dnsbl
{

    public $dnsblLookup = false;
    public $allowedDnsblTypes = array(
        'tor'    => array('tor.dnsbl.sectoor.de'),
        'proxy'  => array('dnsbl.proxybl.org', 'http.dnsbl.sorbs.net', 'socks.dnsbl.sorbs.net'),
        'zombie' => array('xbl.spamhaus.org', 'zombie.dnsbl.sorbs.net'),
        'spam'   => array('b.barracudacentral.org', 'spam.dnsbl.sorbs.net', 'sbl.spamhaus.org'),
        'dialup' => array('dyn.nszones.com')
    );
    public $dnsblType = null;
    public $dnsblCache = null;
    public $dnsblData = null;
    public $log;

    public function __construc($opts, $log)
    {
        if(!isset($log)) return false;
        $this->log = $log;

        foreach($opts as $key => $value){
            $this->$key = $value;
        }

        if($this->dnsblLookup == false)
            return false;

        $this->dnsblType = array_keys($this->allowedDnsblTypes);
    }

    # function: lookup ip address in dns blacklist(s)
    function ipaddrToDnsbl($ipaddr){
        if (isset($this->dnsblCache[$ipaddr]))
            return $this->dnsblCache[$ipaddr];
        else{
            $this->dnsblData = null;
            $reverse_ipaddr = implode('.', array_reverse(explode('.', $ipaddr)));
            foreach ($this->dnsblType as $type)
                foreach($this->allowedDnsblTypes[$type] as $listname)
                    if (checkdnsrr($reverse_ipaddr . '.' . $listname . '.', 'A')){
                        $this->dnsblData = $type;
                        break 2;
                    }

            $this->dnsblCache[$ipaddr] = $this->dnsblData;

            return $this->dnsblData;
        }
    }

    # function: anomaly detection based on dnsbl information
    function detectionDnsbl($ipaddr, $threshold){
        if (!filter_var($ipaddr, FILTER_VALIDATE_IP))
            return null;

        if (isset($this->dnsblCache[$ipaddr])){
            return $threshold;
        }
    }

}