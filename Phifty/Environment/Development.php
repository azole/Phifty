<?php
/**
 *
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Phifty\Environment;
use Universal\Requirement\Requirement;
use Exception;
use ErrorException;


class Development 
{

    static function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    static function exception_handler($e) {
        // var_dump( $e ); 
    }


    // XXX: does not work with E_PARSE error
    // register_shutdown_function(array(__CLASS__,'shutdown_handler')); 
    static function shutdown_handler() {
        if(is_null($e = error_get_last()) === false) { 
            print_r($e);
        }
    }

    static function init($kernel)
    {
        error_reporting(E_ALL);

        // @link http://www.php.net/manual/en/function.set-error-handler.php
        set_error_handler(array(__CLASS__,'exception_error_handler'), E_ALL & ~E_NOTICE );


        // xxx: Can use universal requirement checker.
        //
        // $req = new Universal\Requirement\Requirement;
        // $req->extensions( 'apc','mbstring' );
        // $req->classes( 'ClassName' , 'ClassName2' );
        // $req->functions( 'func1' , 'func2' , 'function3' )

        /* check configs */
        /* check php required extensions */
        if( $configExt = $kernel->config->get('Requirement.Extensions') ) {
            foreach( $configExt as $extName ) {
                if( ! extension_loaded( $extName ) )
                    throw new Exception("Extension $extName is not loaded.");
            }
        }

        // set_exception_handler(array(__CLASS__,'exception_handler') );

        // if firebug supports
        if( $kernel->isCLI ) {
            $kernel->event->register('phifty.after_run', function() use ($kernel) {
                echo 'phifty-', $kernel::VERSION, PHP_EOL;
                echo 'memory usage: ', (int) (memory_get_usage() / 1024  ) , ' KB', PHP_EOL;
                echo 'memory peak usage: ', (int) (memory_get_peak_usage() / 1024 ) , ' KB' . PHP_EOL;
                echo 'duration: ', ceil((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000000 ) , ' microseconds' ;
            });
        }
        // when exception found, forward output to exception render controller.
    }
}
