<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:37:19
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 22:40:13
*/
namespace Suyan\Lorg\Input;

class FileInput implements InputInterface {

    protected $fileName;
    protected $inputStream;

    public function init($source = ''){
        if(file_exists($source))
            $this->fileName = $source;
        else 
            throw new \Exception('file '.$source.' not exist!');

        if(($this->inputStream = fopen($this->fileName,'r')) == false)
            throw new \Exception('file '.$source.' is not readable!');
    }

    public function resetInput(){
        fseek($this->inputStream, 0);
    }

    public function getLine(){
        return trim(fgets($this->inputStream));
    }
}

