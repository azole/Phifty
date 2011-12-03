<?php

namespace Phifty;

class StdErr
{
    public $stream;

    function __construct()
    {
        $this->stream = fopen( 'php://stderr' , 'w' );
    }

    function write( $msg )
    {
        fwrite( $this->stream , $msg );
    }

    function log( $msg )
    {
        fwrite( $this->stream , $msg . "\n" );
    }

    function close()
    {
        fclose( $this->stream );
    }

}




?>
