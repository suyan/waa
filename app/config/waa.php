<?php 

return array(
    'paginate' => '10',
    'upload_dir' => storage_path(). '/upload',
    'supervisor' => array(
        'host' => '127.0.0.1',
        'port' => '9001',
        'name' => 'root',
        'password' => '123qwe',
        'group' => 'waaQueue'
        ),
    // lorg settings
    'config' => array(
        'type' => 'Laravel',
        'Laravel' => 'waa'
        ),
    'log' => array(
        'type' => 'Terminal',
        'Terminal' => ''
        ),
    'input' => array(
        'type' => 'File',
        'File' => app_path().'/libraries/Suyan/Lorg/Data/log_test'
        ),
    'output' => array(
        'type' => 'Terminal',
        'Terminal' => ''
        ),
    'detect' => array(
        'detectMode' => array(
            'chars', //特殊符号
            'phpids', //PHPIDS
            // 'mcshmm', //机器学习，需要大量数据，很慢
            // 'dnsbl',  //DNSBL，慢慢
            // 'geoip',  //地理位置检测，略慢
            'length'  //返回长度
            ),
        'threshold' => 10,
        'summarize' => false,
        ),
    'phpids' => array(
        'phpidsConfigPath' => '',
        'phpidsTmpPath' => app_path().'/storage/tmp',
        'phpidsFilterType' => 'json',
        'phpidsFilterPath' => app_path().'/libraries/Suyan/Lorg/Data/default_filter.json',
        'usePhpidsConverter' => false, // 开启后特别慢
        'addTags' => true,
        ),
    'geoip' => array(
        'geoipFile' => app_path().'/libraries/Suyan/Lorg/Data/GeoLite2-City.mmdb',
        'geoipLookup' => true, // 如果geoip开启检测的话，这个必须设定为true
        ),
    'helper' => array(
        // 'dnsLookup' => true, // 将ip转为domain
        'clientIdent' => 'host', // client唯一性标注
        ),
    'tamper' => array(
        'tamperTest' => false
        ),
    'mcshmm' => array(
        'addVector' => array(
            // 'path', 
            // 'argnames', 
            // 'cookie', 
            // 'agent', 
            // 'all'
            ),
        ),
    'quantify' => array(
        'quantifyType' => array(
            'status', 
            'bytes', 
            // 'replay'  // 要去源站点请求，很慢
            ),
        'target' => ''
        ),
    'anomaly' => array(),
);