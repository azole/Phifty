<?php
namespace Phifty;

use Phifty\Logger;
abstract class Importer 
{
    public $logger;

    function __construct($logPrefix = 'import-')
    {
        $this->logger = new Logger( 'logs' , $logPrefix );
    }

    function info( $msg , $pad = 0 ) 
    {
        $msg = str_repeat(' ',$pad * 4) . $msg;
        echo $msg . "\n";
        $this->logger->info( $msg );
    }

    /* import from a file or a directory */
    abstract function import($target);
}



