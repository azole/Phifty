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

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

class Development 
{

    static function exception_handler($e)
    {
        var_dump( $e ); 
    }


    /**
     * @link http://www.php.net/manual/en/function.set-error-handler.php
     */
    static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        echo "$errno: $errstr @ $errfile:$errline\n";
    }

    static function init($kernel)
    {
        // use Universal\Requirement\Requirement checker
        if( ! class_exists( 'ReflectionObject' ) )
            throw new Exception('ReflectionObject class is not defined. Seems you are running an oooold php.');

        error_reporting(E_ALL | E_STRICT | E_ERROR | E_NOTICE | E_WARNING | E_PARSE);

        set_error_handler('Phifty\Environment\exception_error_handler');


        // xxx: Can use universal requirement checker.
        //
        // $req = new Universal\Requirement\Requirement;
        // $req->extensions( 'apc','mbstring' );
        // $req->classes( 'ClassName' , 'ClassName2' );
        // $req->functions( 'func1' , 'func2' , 'function3' )

        /* check configs */
        /* check php required extensions */
        $configExt = $kernel->config->get('php.extension');
        if( $configExt ) {
            foreach( $configExt as $extName ) {
                if( ! extension_loaded( $extName ) )
                    throw new \Exception("Extension $extName is not loaded.");
            }
        }

        set_exception_handler( array(__CLASS__,'exception_handler') );
        // set_error_handler( array(__CLASS__,'error_handler') );

        // if firebug supports
        $kernel->event->register('phifty.after_run', function() use ($kernel) {
            if( $kernel->isCLI ) {
                echo 'Memory Usage:', (int) (memory_get_usage() / 1024  ) , ' KB', PHP_EOL;
                echo 'Memory Peak Usage:', (int) (memory_get_peak_usage() / 1024 ) , ' KB' . PHP_EOL;
            }
        });
        // when exception found, forward output to exception render controller.
    }
}
