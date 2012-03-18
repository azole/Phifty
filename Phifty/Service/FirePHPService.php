<?php
namespace Phifty\Service;

class FirePHPService 
    implements ServiceInterface
{
    public function register($kernel, $options = array() )
    {
        /**
         * init firephp for development:
         * http://www.firephp.org/HQ/Use.htm
         *
         * if not in CLI mode, include firePHP.
         *
         * $ pear channel-discover pear.firephp.org
         * $ pear install firephp/FirePHPCore 
         **/
        if( ! $kernel->isCLI ) {
            require PH_ROOT . '/vendor/firephp/lib/FirePHPCore/fb.php';
        }

        // if firebug supports
        $kernel->event->register('phifty.after_run', function() use ($kernel) {
            if( function_exists('fb') ) 
            {
                fb( (memory_get_usage() / 1024 / 1024 ) . ' MB'  , 'Memory Usage' );
                fb( (memory_get_peak_usage() / 1024 / 1024 ) . ' MB'  , 'Memory Peak Usage' );
                fb( (time() - $_SERVER['REQUEST_TIME']) , 'Request time' );
            }
        });
    }
}
