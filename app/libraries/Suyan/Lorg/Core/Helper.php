<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-23 19:16:00
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-03 08:57:25
*/

namespace Suyan\Lorg\Core;

class Helper
{

    public $dnsLookup = false; //开启后减慢速度
    public $dnsCache = null;

    // client 
    public $allowedClientIdent = array('host', 'session', 'user', 'logname', 'all');
    public $clientIdent = 'host';

    // session
    public $sessionIdentifiers = array('SID', 'SESSID', 'PHPSESSID', 'JSESSIONID', 'ASP.NET_SessionId');
    

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

    static function httpdataToVector($data, $detectMode, $phpids, $quantify, $mcshmm){
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
                    $val = Helper::implodeRecursive('', $val);

            $cookie = (array_key_exists('Cookie', $data) and ($data['Cookie'] != '-')) ? $data['Cookie'] : '';
            $agent = (array_key_exists('User-Agent', $data) and ($data['User-Agent'] != '-')) ? $data['User-Agent'] : '';

            $request = null;
            
            if (!$quantify->onlyCheckWebapps or (preg_match("/.*(" . implode('|', $quantify->webAppExtensions) . ")$/", $path)))
                $request['query'] = !empty($parameters) ? $parameters : null;
            
            if ($mcshmm->addVector)
                foreach ($mcshmm->addVector as $vector)
                    $request[$vector] = (!empty($$vector)) ? $$vector : null;

            if ($phpids->usePhpidsConverter and isset($request)) 
                if (in_array('chars', $detectMode) or in_array('mcshmm', $detectMode))
                    array_walk_recursive($request, array($phpids,'convertUsingPhpids'));
            
            return array($request, $path);
        } else
            return null;
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

    static function isDomainOrIp($str){
        return (preg_match("/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/i", $str)
            || preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/", $str));
    }
}