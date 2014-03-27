<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-22 18:35:57
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 22:39:31
*/
namespace Suyan\Lorg\Output;
class Output implements OutputInterface
{    
    protected $output;
    public function __construct($output){
        $output = 'Suyan\Lorg\Output\\'.$output.'Output';
        $this->output = new $output;
    }

    public function init($source = ''){
        return $this->output->init($source);
    }

    public function writeVector($vector){
        return $this->output->writeVector($vector);
    }

    public function writeSummarize($summarize){
        return $this->output->writeSummarize($summarize);
    }
}