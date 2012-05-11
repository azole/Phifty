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

        if( extension_loaded('xdebug') ) {
            ini_set('xdebug.cli_color', true );
            ini_set('xdebug.show_local_vars', true );
            ini_set('xdebug.var_display_max_data', 64 );
        }

        $commands = array();
        $commands['exit'] = function() {
            exit(0);
        };

        $kernel = kernel();
        while(1) {
            try {
                $text = $this->ask('>>');

                if( isset($commands[$text]) ) {
                    call_user_func( $commands[$text] );
                }

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
            catch ( Exception $e ) {
                $printer = new \Exception\ConsolePrinter( $e );
                echo $printer;
            }
        }
    }
}


