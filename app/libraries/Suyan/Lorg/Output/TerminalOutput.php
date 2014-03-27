<?php

namespace Suyan\Lorg\Output;
class TerminalOutput implements OutputInterface
{
    public function init($source = '')
    {
        return true;
    }
    public function writeVector($vector)
    {
        var_dump($vector);
        echo "\n";
    }
    public function writeSummarize($summarize)
    {
        var_dump($summarize);
        echo "\n";
    }
}
