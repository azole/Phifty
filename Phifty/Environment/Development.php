<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\Environment;

class Development 
{
    static function init($app)
    {
        if( ! class_exists( 'ReflectionObject' ) )
            throw new Exception('ReflectionObject class is not defined. Seems you are running an oooold php.');
        error_reporting(E_ALL | E_STRICT | E_ERROR | E_NOTICE | E_WARNING | E_PARSE);

        /**
         * init firephp for development:
         * http://www.firephp.org/HQ/Use.htm
         *
         * if not in CLI mode, include firePHP.
         **/
        if( ! $app->isCLI ) {
            require PH_ROOT . '/dists/firephp/lib/FirePHPCore/fb.php';


        /* check configs */
        /* check php required extensions */
        $configExt = $app->config('php.extension');
        if( $configExt ) {
            foreach( $configExt as $extName ) {
                if( ! extension_loaded( $extName ) )
                    throw new \Exception("Extension $extName is not loaded.");
            }
        }

    }
}
