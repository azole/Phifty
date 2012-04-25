<?php
namespace Phifty\Environment;

class CommandLine
{
    static function init($kernel)
    {
        mb_internal_encoding('UTF-8');
        define( 'CLI_MODE' , $kernel->isCLI );
        if( $kernel->isCLI ) {
            ini_set('output_buffering ', '0');
            ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
        } else {
            ob_start();
            $s = $kernel->session; // build session object
        }

    }
}
