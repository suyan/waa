<?php
return array(
    'log' => array(
        'default' => 'Terminal',
        'Terminal' => ''
        ),
    'input' => array(
        'default' => 'File',
        'File' => app_path().'/libraries/Suyan/Lorg/Data/log_test'
        ),
    'output' => array(
        'default' => 'Terminal',
        'Terminal' => ''
        ),
    'detect' => array(
        'detectMode' => array(
            'chars', //特殊符号
            'phpids', //PHPIDS
            'mcshmm', //机器学习，需要大量数据，很慢
            'dnsbl',  //DNSBL，慢慢
            'geoip',  //地理位置检测，略慢
            'length'  //返回长度
            ),
        'threshold' => 10
        ),
    'phpids' => array(
        'phpidsConfigPath' => '',
        'phpidsTmpPath' => app_path().'/storage/tmp',
        'phpidsFilterPath' => app_path().'/libraries/Suyan/Lorg/Data/default_filter.json',
        'usePhpidsConverter' => true, // 开启后特别慢
        'addTags' => true,
        ),
    'geoip' => array(
        'geoipFile' => app_path().'/libraries/Suyan/Lorg/Data/GeoLite2-City.mmdb',
        'geoipLookup' => true, // 如果geoip开启检测的话，这个必须设定为true
        'lofMinLearn' => 1, //测试用
        ),
    'helper' => array(
        'dnsLookup' => true, // 将ip转为domain
        'clientIdent' => 'host', // client唯一性标注
        ),
    'tamper' => array(
        'tamperTest' => true
        ),
    'mcshmm' => array(
        'hmmMinLearn' => 1, // 测试用
        'addVector' => array(
            'path', 
            'argnames', 
            'cookie', 
            'agent', 
            'all'
            ),
        ),
    'quantify' => array(
        'quantifyType' => array(
            'status', 
            'bytes', 
            'replay'  // 要去源站点请求，很慢
            ),
        'target' => ''
        ),
    'anomaly' => array(),
);