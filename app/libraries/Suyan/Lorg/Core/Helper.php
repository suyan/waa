<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-23 19:16:00
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 13:14:53
*/

namespace Suyan\Lorg\Core;

class Helper
{

    public $dnsLookup = false; //开启后减慢速度
    public $dnsCache = null;
    

    public function __construct($opts, $log){
        if(!isset($log)) return false;
        $this->log = $log;

        foreach($opts as $key => $value){
            $this->$key = $value;
        }
    }

    // function: convert ip address to hostname, if possible
    function ipaddrToHostname($ipaddr){
        // return argument, if it already contains a hostname
        if (preg_match("/^.*[a-zA-Z]$/", $ipaddr))
            return $ipaddr;

        if (isset($this->dnsCache[$ipaddr]))
            $hostname = $this->DnsCache[$ipaddr];
        else{
            $reverse_ipaddr = implode('.', array_reverse(explode('.', $ipaddr))) . '.in-addr.arpa';
            $record = dns_get_record($reverse_ipaddr, DNS_PTR);
            if (isset($record[0]['target']))
                $hostname = $record[0]['target'];
            else
                $hostname = $ipaddr;
            $this->DnsCache[$ipaddr] = $hostname;
        }

        return $hostname;
    }

    // function: convert hostname to ip address, if possible
    function hostnameToIpaddr($hostname){
        if (filter_var($hostname, FILTER_VALIDATE_IP))
            return $hostname;

        if (isset($this->DnsCache[$hostname]))
            $ipaddr = $this->DnsCache[$hostname];
        else{
            $record = dns_get_record($hostname, DNS_A);
            if (isset($record[0]['ip']))
                $ipaddr = $record[0]['ip'];
            else
                $ipaddr = $hostname;

            $this->DnsCache[$hostname] = $ipaddr;
        }
        return $ipaddr;
    }

    // 转换日志行到HTTP-data数组
    static function loglineToHttpdata($line, $regex){
        if (preg_match($regex['regex_string'], $line, $matches) !== 1)
          return false;
        reset($regex['regex_fields']);
        for ($n = 0; $n < $regex['num_fields']; ++$n){
            $field = each($regex['regex_fields']);
            $data[$field['key']] = $matches[$n + 1];
        }
        return $data;
    }

    // function: convert apache format strings to description
    static function formatstrToDesc($field){
        $orig_val_default = array('s', 'U', 'T', 'D', 'r');
        $trans_names = array(
            '%' => '',
            'a' => 'Remote-IP',
            'A' => 'Local-IP',
            'B' => 'Bytes-Sent-X',
            'b' => 'Bytes-Sent',
            'c' => 'Connection-Status', // <= 1.3
            'C' => 'Cookie', // >= 2.0
            'D' => 'Time-Taken-MS',
            'e' => 'Env-Var',
            'f' => 'Filename',
            'h' => 'Remote-Host',
            'H' => 'Request-Protocol',
            'i' => 'Request-Header',
            'I' => 'Bytes-Received', // requires mod_logio
            'l' => 'Remote-Logname',
            'm' => 'Request-Method',
            'n' => 'Note',
            'o' => 'Reply-Header',
            'O' => 'Bytes-Sent', // requires mod_logio
            'p' => 'Port',
            'P' => 'Process-Id', // {format} >= 2.0
            'q' => 'Query-String',
            'r' => 'Request',
            's' => 'Status',
            't' => 'Date',
            'T' => 'Time-Taken-S',
            'u' => 'Remote-User',
            'U' => 'Request-Path',
            'v' => 'Server-Name',
            'V' => 'Server-Name-X',
            'X' => 'Connection-Status', // >= 2.0
            );

        foreach($trans_names as $find => $name){
            if(preg_match("/^%([!\d,]+)*([<>])?(?:\\{([^\\}]*)\\})?$find$/", $field, $matches)){
                if (!empty($matches[2]) and $matches[2] === '<' and !in_array($find, $orig_val_default, true))
                    $chooser = "Original-";
                elseif (!empty($matches[2]) and $matches[2] === '>' and in_array($find, $orig_val_default, true))
                    $chooser = "Final-";
                else
                    $chooser = '';
                $name = "{$chooser}" . (!empty($matches[3]) ? "$matches[3]" : $name) . (!empty($matches[1]) ? "($matches[1])" : '');
                break;
            }
        }
        if(empty($name))
            return $field;

        return $name;
    }

    // 将自定义的日志格式转换成正则表达式
    static function formatToRegex($format){
        $regex_fields = null;
        $regex_string = null;
        $num_fields = null;
        $format = preg_replace(array('/[ \t]+/', '/^ /', '/ $/'), array(' ', '', ''), $format);
        $regex_elements = array();

        foreach(explode(' ', $format) as $element){
            $quotes = preg_match('/^\\\"/', $element);
            if($quotes)
                $element = preg_replace(array('/^\\\"/', '/\\\"$/'), '', $element );

            $regex_fields[Helper::formatstrToDesc($element)] = null;

            if($quotes){
                if ($element == '%r'
                or (preg_match('/{(.*)}/', $element)))
                    $x = '\"([^\"\\\\]*(?:\\\\.[^\"\\\\]*)*)\"';
                else
                    $x = '\"([^\"]*)\"';
            }
            elseif ( preg_match('/^%.*t$/', $element) )
                $x = '(\[[^\]]+\])';
            else
                $x = '(\S*)';

            $regex_elements[] = $x;
        }

        $regex_string = '/^' . implode(' ', $regex_elements ) . '$/';
        return array(
            'regex_fields' => $regex_fields,
            'regex_string' => $regex_string,
            'num_fields' => count($regex_fields)
            );
    }

    # function: substitute alphanumeric elements of a string
    static function convertAlphanumeric($str){
        $str_subst = preg_replace('/[\p{L}äöüÄÖÜß]/', 'A', $str); // replace letters a-Z with A
        $str_subst = preg_replace('/\d/', 'N', $str_subst); // replace digits 0..9 with N
        return $str_subst;
    }

    # function: pick one or more random entries out of an multi-dimensional array
    static function arrayRandMulti($array, $limit = 2){
        uksort($array, __NAMESPACE__.'\Helper::callbackRand');
        return array_slice($array, 0, $limit, true); 
    }

    # function: callback for function array_rand_multi()
    static function callbackRand(){ 
        return rand() > rand();
    }

    // function: convert 'standard english format' to unix timestamp
    static function apachedateToTimestamp($time){
        list($d, $M, $y, $h, $m, $s, $z) = sscanf($time, "[%2d/%3s/%4d:%2d:%2d:%2d %5s]");
        return strtotime("$d $M $y $h:$m:$s $z");
    }

    static function onlineVariance(&$avg, &$var, &$index, $value){
        $index++;
        $delta = $value - $avg;
        $avg += $delta/$index;
        $var += $delta * ($value - $avg);
    }

    # function: remove alphanumeric and other less suspicious chars from string
    static function removeAlphanumeric($str){
        $str_subst = preg_replace('#\\.\\.#','**', $str); 
        $str_subst = preg_replace('/[\p{L}äöüÄÖÜß\d]/', '', $str_subst); 
        return $str_subst;
    }

    # function: join (multi-dimensional) array elements with a string
    static function implodeRecursive($glue, array $multi_array){
        $str = '';
        foreach ($multi_array as $key => $val)
            $str .= is_array($val) ? Helper::implodeRecursive($glue, $val) : $glue . $val;
        return $str;
    }
}