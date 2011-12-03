<?php
namespace Phifty;


class Profiler
{
    var $timeFrom = array();
    var $timeTo   = array();
    var $currentTaskName = array();

    function start($taskName)
    {
        $this->timeFrom[ $takeName ] = microtime();
        array_push( $this->currentTaskName , $taskName );
    }

    function end()
    {
        $taskName = array_pop( $this->currentTaskName );
        $this->timeTo[ $taskName ] = microtime();
    }

    function report()
    {
        $ts = $this->timeTo - $this->timeFrom;
        print <<<REPORT
<!-- time: $ts -->
REPORT;
    }
}


?>
