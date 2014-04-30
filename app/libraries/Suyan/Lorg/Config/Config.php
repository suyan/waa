<?php
namespace Suyan\Lorg\Config;

class Config implements ConfigInterface
{    
    protected $config;
    public function __construct($opts){
        if (!isset($opts['type']))
            throw new \Exception("invalid opts of config");
        $type = $opts['type'];
        $config = 'Suyan\Lorg\Config\\'.$type.'Config';

        if (!class_exists($config)) 
            throw new \Exception("$config doesn't exists");

        if (!isset($opts[$type])) 
            throw new \Exception("invalid opts of config");
        $this->config = new $config($opts[$type]);
    }

    public function get($name, $default = ''){
        return $this->config->get($name, $default);
    }

    public function set($name, $value){
        return $this->config->set($name, $value);
    }
}