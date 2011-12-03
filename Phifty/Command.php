<?php

namespace Phifty;

use \Phifty\GetOpt;

class Command 
{
    public $cascading = false;
    public $argv;
	public $args;
    public $script;

    public $shortOpts = '';
    public $longOpts  = array();


    public $verbose = false;
    public $debug = false;

	function __construct()
	{
        global $argv;

        /* save original argv */
        $this->argv = array_merge(array(), $argv);



        // lets modify argv
		$this->script  = array_shift( $argv ); /* script name */
		$this->command = array_shift( $argv ); /* command */
        $this->args = array_merge(array(),$argv);

        $this->args = array_filter( $this->argv , function($val) {
            return ! preg_match('/^-/', $val);
        });
	}

    function getOptions()
    {
        global $argv;

        $opt = new \Phifty\GetOpt( $this->shortOpts , $this->longOpts  );
        $ret = $opt->parse( $argv );
        if( $ret )
            return (object) $ret;
        return $ret;
    }

    function execute( $cmd )
    {
        echo "Executing: " . $cmd . "\n";
        system( $cmd );
    }


	// XXX: use ProcessPipe here
    function pExecute($cmd, $input='') 
    {
        $proc=proc_open($cmd, array(
            0 => array('pipe', 'r'), 
            1 => array('pipe', 'w'), 
            2 => array('pipe', 'w')), $pipes); 

        if( $input )
            fwrite($pipes[0], $input);

        fclose($pipes[0]); 

        $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]); 
        $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]); 

        $rtn=proc_close($proc); 

        return array(
            'stdout'=>$stdout, 
            'stderr'=>$stderr, 
            'return'=>$rtn 
        ); 
    } 


    function mkdir($dir)
    {
        $this->log( "Creating Directory $dir." );
        mkdir( $dir );
    }


    function debug( $msg )
    {
        if( $this->debug )
            echo '[DEBUG] ' . $msg . "\n";
    }


    function log( $msg )
    {
        echo $msg . "\n";
    }

    function info( $msg )
    {
        if( $this->verbose )
            echo '[INFO] ' . $msg . "\n";
    }

    function doExit( $exitMsg )
    {
        die( $exitMsg );
        // echo $exitMsg;
        // exit(1);
    }


    /*
    get argument after script name.

        ./phifty.php [command name] [arg1] [arg2] [arg3]
     */
    function getArg( $index )
    {
        return @$this->args[ $index ];
    }




}




?>
