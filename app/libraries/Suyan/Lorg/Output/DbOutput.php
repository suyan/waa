<?php

namespace Suyan\Lorg\Output;
class DbOutput implements OutputInterface
{

    protected $vector_db;
    protected $host_db;
    protected $host_id;

    public function init($source = '')
    {
        $this->vector_db = $source['vector_db'];
        $this->host_db = $source['host_db'];
        $this->host_id = $source['host_id'];
        return true;
    }
    public function writeVector($vector)
    {
        $vector = array_merge(array('host_id' => $this->host_id),$vector);
        $this->vector_db->insert($vector);
    }
    public function writeSummarize($summarize)
    {
        $this->host_db->where('id',$this->host_id)->update(array(
            'line_count' => $summarize['line_count'],
            'attack_count' => $summarize['attack_count'],
            'impact_count' => $summarize['impact_count'],
            'start_time' => date('Y-m-d H:i:s', $summarize['start_time']),
            'end_time' => date('Y-m-d H:i:s', $summarize['end_time']),
            ));
    }
}
