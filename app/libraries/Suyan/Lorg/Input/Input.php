<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:35:57
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 22:40:06
*/
namespace Suyan\Lorg\Input;
class Input implements InputInterface
{    
    protected $input;
    public function __construct($input){
        $input = 'Suyan\Lorg\Input\\'.$input.'Input';
        $this->input = new $input;
    }

    public function init($source = ''){
        return $this->input->init($source);
    }

    public function resetInput(){
        return $this->input->resetInput();
    }

    public function getLine(){
        return $this->input->getLine();
    }
}