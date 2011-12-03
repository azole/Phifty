<?php
namespace Phifty;

class Task
{
    public $name;
    public $start;
    public $end;
    public function __construct( $name )
    {
        $this->name = $name;
        $this->start = microtime( true );
    }

    public function end()
    {
        $this->end = microtime( true );
    }

    public function getDuration() 
    {
        return ($this->end - $this->start);
    }

    public function report()
    {
        if( $this->name )
            echo "Task: {$this->name} ";
        echo "Duration: " . ($this->end - $this->start) . " sec.\n";
    }
}

class TaskTimer 
{
    public $tasks = array();

    function start( $taskname = '' )
    {
        $t = new Task( $taskname );
        array_push( $this->tasks, $t );
    }

    function end()
    {
        $t = array_pop( $this->tasks );
        if( $t ) {
            $t->end();
            return $t;
        }
    }
}

?>
