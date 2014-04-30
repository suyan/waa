<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:35:57
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-30 10:02:47
*/
namespace Suyan\Lorg\Input;

/**
 * Input的驱动类，负责实例化真正的输入
 */
class Input implements InputInterface
{    
    protected $input;
    public function __construct($opts){

        // 检查输入类型
        if (!isset($opts['type'])) 
            throw new Exception("invalid opts of input");        
        $input = 'Suyan\Lorg\Input\\'.$type.'Input';

        if (!class_exists($input)) 
            throw new Exception("$input doesn't exists");

        // 实例化输入类型
        if (!isset($opts[$opts['type']])) 
            throw new Exception("invalid opts of input");
        $this->input = new $input($opts[$opts['type']]);
    }

    public function resetInput(){
        return $this->input->resetInput();
    }

    public function getLine(){
        return $this->input->getLine();
    }
}