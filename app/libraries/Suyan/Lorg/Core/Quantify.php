<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-24 12:22:44
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-28 20:21:30
*/

namespace Suyan\Lorg\Core;

class Quantify
{
    // quantify
    public $quantifyType = array('status', 'bytes', 'replay');
    public $target = '';

    public $webAppExtensions = 
        array('cgi', 'php[3-5]?', 'phtml', 'pl', 'jsp', 'aspx?', 'cfm', 'exe');
    public $onlyCheckWebapps = false;
    public $replayCount = array();
    public $maxReplayPerClient = 1000;

    public $allowedHttpMethods = 
        array('HEAD', 'GET', 'POST', 'PUT', 'TRACE', 'OPTIONS', 'CONNECT');
    public $maxSessionDuration = 3600;

    public $log;
    public $geoip;

    public function __construct($opts, $log, $geoip){
        if(!isset($log)) return false;
        $this->log = $log;

        if(!isset($geoip)) return false;
        $this->geoip = $geoip;

        foreach($opts as $key => $value){
            $this->$key = $value;
        }

        // 检查quantify
        if(in_array('replay', $this->quantifyType)){
            if(!Helper::isDomainOrIp($this->target)){
                unset($this->quantifyType[array_search('replay', $this->quantifyType)]);
            } else {
                if (!preg_match("/^https?:\/\//", $this->target))
                    $this->target = 'http://' . $this->target;
                $body = @file_get_contents( $this->target . '/', false);
                if ($body === FALSE)
                    $this->log->log('无法链接到目标主机');    
            }    
        }
    }

    # function: try to evaluate the success of attacks
    function attackQuantification($request, $data, $path, $client, &$dataset){

        $status = isset($data['Final-Status']) ? $data['Final-Status'] : null;
        $bytes = isset($data['Bytes-Sent']) ? (($data['Bytes-Sent'] != '-') ? $data['Bytes-Sent'] : 0) : null;


        // success evaluation based on status-codes
        if (in_array('status', $this->quantifyType)){
            if (preg_match("/^(404)+$/", $status))
                $success[] = 'unsuccessful scan?';
            if (preg_match("/^(401|403)+$/", $status))
                $success[] = 'unsuccessful http-auth';
            if (preg_match("/^(400|408|503)$/", $status))
                $success[] = 'denial of service?';
            if (preg_match("/^(500)$/", $status))
                $success[] = 'buffer overflow?';
            if (preg_match("/^(414)$/", $status))
                $success[] = 'unsuccessful buffer overflow?';

            if (preg_match("/^(200)+$/", $status)
                and isset($dataset['query'][$path]['chars']['clients'])
                and (count($dataset['query'][$path]['chars']['clients']) == 1)
                and (preg_match("/.*(" . implode('|', $this->webAppExtensions) . ")$/", $path)))
                    $success[] = 'potential webshell?';
        }

        if (in_array('bytes', $this->quantifyType)){
            $lof_bytes = 0;
            $lof_data = null;

            if (isset($this->geoip->lofCache['lof'][$path][$bytes]))
                $lof_bytes = $this->geoip->lofCache['lof'][$path][$bytes];
            else{
                if (isset($dataset['query'][$path]['bytes']))
                    $lof_data = &$dataset['query'][$path]['bytes'];

                if (isset($bytes) and !is_null($lof_data)){
                    for ($lof_neighbors=$this->geoip->lofMinptsLb; 
                        $lof_neighbors <= $this->geoip->lofMinptsUb; $lof_neighbors++){
                        $lof = new LOF($lof_data, $lof_neighbors, $this->geoip->lofCache);

                        $lof_values[$lof_neighbors] = $lof->run($client, $bytes);
                    }

                    $lof_bytes = round(max($lof_values));

                    $this->geoip->lofCache['lof'][$path][$bytes] = $lof_bytes;
                }
            }

            if ($lof_bytes > 1)
                $success[] = 'Bytes-sent outlier by factor ' . round($lof_bytes);
        }
        
        $max_replay_not_reached = (isset($this->replayCount[$client]) ? 
            ($this->replayCount[$client] < $this->maxReplayPerClient) : true);

        if (in_array('replay', $this->quantifyType) and $max_replay_not_reached){
            $this->replayCount[$client] = isset($replayCount[$client]) ? 
                $this->replayCount[$client]++ : 0;

            if (preg_match("/^(\S+) (.*?) HTTP\/[0-9]\.[0-9]\z/", $data['Request'], $match))
                $payload = $match[2];
            else
                $payload = $data['Request'];

            $opts = array('http' => array('user_agent' => 'LORG active-replay quantification', 'timeout' => 3, 'ignore_errors' => true));
            $body = @file_get_contents($this->target . $path, false, stream_context_create($opts));

            $signatures = array(
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'File disclosure: UNIX /etc/passwd' => 'root\:x\:0\:0\:.+\:[0-9a-zA-Z\/]+',
                'File disclosure: Windows boot.ini' => '\[boot loader\](.*)\[operating systems\]',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'File disclosure: Apache access log' => '0\] "GET \/',
                'File disclosure: Apache error log' => '\[error\] \[client ',
                'File disclosure: IIS access log' => '0, GET, \/',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'File disclosure: Java source' => 'import java\.',
                'File disclosure: C/C++ source' => '#include',
                'File disclosure: Shell script' => '#\!\/(.*)bin\/',
                'File disclosure: PHP source' => '\<\? ?php(.*)\?\>', # better mime:application/x-httpd-php-source
                'File disclosure: JSP source' => '\<%@(.*)%\>',
                'File disclosure: ASP source' => '\<%(.*)%\>',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'File disclosure: DSA/RSA private key' => '\-\-\-\-\-BEGIN (D|R)SA PRIVATE KEY\-\-\-\-\-',
                'File disclosure: SQL configuration/logs' => 'ADDRESS\=\(PROTOCOL\=',
                'File disclosure: web.xml config file' => '\<web\-app',
                'File disclosure: SVN RCS data' => 'svn\:special svn',
                'File disclosure: MySQL dump' => '\-\- MySQL dump',
                'File disclosure: phpMyAdmin dump' => ' phpMyAdmin (My)?SQL(\-| )Dump',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'Info disclosure: Environment variables' => 'REQUEST_URI\=' . preg_quote($path, '/'),
                'Info disclosure: directory listing' => '[iI]ndex [oO]f(.*)\/"\>Parent Directory\<\/a\>',
                'Info disclosure: phpinfo() page' => '\<title\>phpinfo\(\)\<\/title\>\<meta name\=',
                'Info disclosure: Apache mod_status' => '(\<title\>(Apache Status|Server Information)\<\/title\>)(.*)Server Version\:',
                'Info disclosure: ODBC password' => '\(Data Source\=\|Driver\=\|Provider\=\)(.*)(;Password\=|;Pwd\=)',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'Info disclosure: PHP exception' => 'PHP (Notice|Warning|Error)',
                'Info disclosure: Java IO exception' => 'java.io.FileNotFoundException: ',
                'Info disclosure: Python IO exception' => 'Traceback (most recent call last):',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'Info disclosure: file system path' => 'Call to undefined function.*\(\) in \/',
                'Info disclosure: web root path' => '\: failed to open stream\: ', # file inclusion attempt?
                'Info disclosure: file inclusion error: ' => 'Warning(?:\<\/b\>)?\:\s+(?:include|require)(?:_once)?\(', # file inclusion attempt?
                'Info disclosure: DB connection error' => '(mysql|pgp|sqlite|mssql)_p?(connect|open|query)\(',
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                'Info disclosure: Access error (1)' => 'Sybase message\:',
                'Info disclosure: Access error (2)' => 'Syntax error in query expression',
                'Info disclosure: Access error (3)' => 'Data type mismatch in criteria expression\.',
                'Info disclosure: Access error (4)' => 'Microsoft JET Database Engine',
                'Info disclosure: Access error (5)' => '\[Microsoft\]\[ODBC Microsoft Access Driver\]',
                'Info disclosure: ASP / MSSQL error (1)' => 'System\.Data\.OleDb\.OleDbException',
                'Info disclosure: ASP / MSSQL error (2)' => '\[SQL Server\]',
                'Info disclosure: ASP / MSSQL error (3)' => '\[Microsoft\]\[ODBC SQL Server Driver\]',
                'Info disclosure: ASP / MSSQL error (4)' => '\[SQLServer JDBC Driver\]',
                'Info disclosure: ASP / MSSQL error (5)' => '\[SqlException',
                'Info disclosure: ASP / MSSQL error (6)' => 'System\.Data\.SqlClient\.SqlException',
                'Info disclosure: ASP / MSSQL error (7)' => 'Unclosed quotation mark (before|after) the character string',
                'Info disclosure: ASP / MSSQL error (8)' => '\'80040e14\'',
                'Info disclosure: ASP / MSSQL error (9)' => 'mssql_query\(\)',
                'Info disclosure: ASP / MSSQL error (10)' => 'odbc_exec\(\)',
                'Info disclosure: ASP / MSSQL error (11)' => 'Microsoft OLE DB Provider for',
                'Info disclosure: ASP / MSSQL error (12)' => 'Incorrect syntax near',
                'Info disclosure: ASP / MSSQL error (13)' => 'Syntax error in string in query expression',
                'Info disclosure: ASP / MSSQL error (14)' => 'Procedure \'\[^\'\]+\' requires parameter \'\[^\'\]+\'',
                'Info disclosure: ASP / MSSQL error (15)' => 'ADODB\.(Field \(0x800A0BCD\)\<br\>|Recordset\')',
                'Info disclosure: ASP / MSSQL error (16)' => 'Unclosed quotation mark before the character string',
                'Info disclosure: Coldfusion SQL error' => '\[Macromedia\]\[SQLServer JDBC Driver\]',
                'Info disclosure: DB2 error' => '(SQLCODE|DB2 SQL error\:|SQLSTATE|\[CLI Driver\]\[DB2\/6000\])',
                'Info disclosure: DML error (1)' => '\[DM_QUERY_E_SYNTAX\]',
                'Info disclosure: DML error (2)' => 'has occurred in the vicinity of\:',
                'Info disclosure: DML error (3)' => 'A Parser Error \(syntax error\)',
                'Info disclosure: Generic SQL error' => '(INSERT INTO|SELECT|UPDATE) \.\*?( (FROM|SET) \.\*?)?',
                'Info disclosure: Informix error (1)' => 'com\.informix\.jdbc',
                'Info disclosure: Informix error (2)' => 'Dynamic Page Generation Error\:',
                'Info disclosure: Informix error (3)' => 'An illegal character has been found in the statement',
                'Info disclosure: Informix error (4)' => '\<b\>Warning\<\/b\>\:  ibase_',
                'Info disclosure: Informix error (5)' => 'Dynamic SQL Error',
                'Info disclosure: Java SQL error (1)' => 'java\.sql\.SQLException',
                'Info disclosure: Java SQL error (2)' => 'Unexpected end of command in statement',
                'Info disclosure: MySQL error (1)' => 'supplied argument is not a valid MySQL',
                'Info disclosure: MySQL error (2)' => 'Column count doesn\'t match value count at row',
                'Info disclosure: MySQL error (3)' => 'mysql_fetch_array\(\)',
                'Info disclosure: MySQL error (4)' => 'on MySQL result index',
                'Info disclosure: MySQL error (5)' => 'You have an error in your SQL syntax(;| near)',
                'Info disclosure: MySQL error (5)' => 'MySQL server version for the right syntax to use',
                'Info disclosure: MySQL error (7)' => '\[MySQL\]\[ODBC',
                'Info disclosure: MySQL error (8)' => 'the used select statements have different number of columns',
                'Info disclosure: MySQL error (9)' => 'Table \'[^\']+\' doesn\'t exist',
                'Info disclosure: MySQL error (10)' => 'DBD\:\:mysql\:\:(db|st)(.*)failed',
                'Info disclosure: ORACLE error (6)' => '(PLS|ORA)\-[0-9][0-9][0-9][0-9]',
                'Info disclosure: PostgreSQL error (1)' => 'PostgreSQL query failed\:',
                'Info disclosure: PostgreSQL error (2)' => 'supplied argument is not a valid PostgreSQL result',
                'Info disclosure: PostgreSQL error (3)' => 'pg_(exec|query)\(\) \[\:');

            if (is_array($request['query'])){
                foreach ($request['query'] as $parameter => $value){
                    if (preg_match('/\<script/', $value))
                        $signatures['XSS might have been sucessful'] = preg_quote($value, '/');
                }
            }

            foreach ($signatures as $key => $val){
                if (preg_match('/' . $val . '/', $body)){
                    $success[] = $key;
                    break;
                }
            }
        }

        return(isset($success) ? implode(' | ', $success) : '-');
    }

    function sessionIdentification($client, $action, $last_action){
        $new_session = false;

        if (isset($last_action)){
            $delay = strtotime($action->date) - strtotime($last_action->date);


            if ($delay > $this->maxSessionDuration)
                $new_session = 'max_session_duration_exceeded';

            $agent = isset($action->data['User-Agent']) ? $action->data['User-Agent'] : '';
            $last_agent = isset($last_action->data['User-Agent']) ? 
                $last_action->data['User-Agent'] : '';

            if ($agent != $last_agent){
                if (($delay > (60 + $client->avg_time_delay + 3 * $client->std_time_delay))
                    and ($action->path != $last_action->path))
                    $new_session = 'user_agent_change';
            }
        }else 
            $new_session = 'client_first_seen';

        return $new_session;
    }

    # function: classify clients and their sessions, optionally create a nice pchart
    function sessionClassification(&$clients, $requestCount){        
        foreach ($clients as $name => &$client){
            foreach ($client->actions as &$action){
                $client->reset_properties($this, $action);
            }

            $client->classify();

            $last_action = null;

            foreach ($client->actions as &$action){
                if ($this->sessionIdentification($client, $action, $last_action))
                    $session = $action->new_session = new Session();

                # aggregate data used to classify current session as spawned by human or machine
                $session->reset_properties($this, $action);

                $last_action = $action;
            }

            foreach ($client->actions as &$action){
                if (isset($action->new_session)){
                    if (is_object($action->new_session)){
                        $action->new_session = $action->new_session->classify($requestCount);
                        if (!in_array($action->new_session, $client->classification))
                            $client->classification[] = $action->new_session;
                    }
                }
            }
        }
    }

    function aggregateBytes($data, $path, $vector, $client, &$dataset){
        if (!isset($vector[0]['query']))
            return null;

        if (isset($path) and isset($data['Bytes-Sent'])){
            // create reference to the bytes-sent list for the current path
            $bytes = &$dataset['query'][$path]['bytes'];

            // every client is only allowed to contribute once, to avoid test data poisioning
            if (isset($bytes[$client]))
                return null;

            // if no samples retreived yet or number of samples below maximum times ten
            if (!isset($bytes) or (count($bytes) < $this->geoip->lofMaxLearn * 10)){
                // add value for current bytes-sent to dataset
                $bytes[$client] = ($data['Bytes-Sent'] != '-') ? 
                    $data['Bytes-Sent'] + 0.01 * rand(0,99) : 0;
            }
        }
    }
}