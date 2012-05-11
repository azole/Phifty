<?php
namespace Phifty\Command;
use CLIFramework\Command;

class ConsoleCommand extends Command
{

    function brief() { return 'Simple REPL Console.'; }
    
    function execute()
    {

        set_error_handler(function( $errno, $errstr, $errfile, $errline, $errcontext ) { 
            print_r( $errno, $errstr );
            return false;
        });

        $k = kernel();
        while(1) {
            $text = $this->ask('>');
            $var = null;
            $return = eval($text);
            if( $return ) {
                // parse text and dump the value.
                var_dump($return);
            }
            else {
                if( preg_match('#^\s*\$(\w+)#i',$text,$regs) ) {
                    $__n = $regs[1];
                    var_dump( $$__n );
                }
            }
        }
    }
}


