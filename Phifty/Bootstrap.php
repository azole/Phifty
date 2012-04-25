<?php
namespace Phifty;


/** 
 * Script for phifty kernel bootstrap
 *
 * load config file and bootstrap
 *
 * @author c9s <cornelius.howl@gmail.com>
 */
class Bootstrap
{
    function __construct( $env = null , $configPath = null )
    {
        $env = $env ?: getenv('PHIFTY_ENV') ?: 'dev';

        // initialize phifty config
        if( null == $configPath ) {
            $configPath = 'config/' . $env . '.php';
        }


    }
}





