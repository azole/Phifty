<?php
/*
 * This file is part of the {{ }} package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty;

use Exception;
use Phifty\FileUtils;
use Phifty\Singleton;

class PluginManager extends Singleton
{

    /**
     * plugin object stack
     */
    public $plugins = array();
    
    function __construct()
    {

    }

    function isLoaded( $name )
    {
        return isset( $this->plugins[ $name ] );
    }

    function getList()
    {
        return array_keys( $this->plugins );
    }

    function getPlugins()
    {
        return array_values( $this->plugins );
    }

    function hasPluginDir( $name )
    {
        $relpath = FileUtils::path_join( 'plugins' , $name , $name ) . '.php';
        if( file_exists( PH_APP_ROOT . DIRECTORY_SEPARATOR . $relpath) 
            || file_exists( PH_ROOT . DIRECTORY_SEPARATOR . $relpath ) )
            return true;
        return false;
    }

    /**
     * load from plugin list
     */
    function loadFromList( $pluginList )
    {
        try {
            foreach( $pluginList as $name => $config ) {
                $this->load( $name , $config );
                /* add plugin name to loader list */
                # $loader->addPlugin( $name );
            }
        }
        catch( Exception $e )
        {
            die( $e->getMessage() );
        }
    }


    /**
     * has plugin 
     */
	function hasPlugin( $name )
	{
		return isset($this->plugins[ $name ]);
	}


    /**
     * get plugin object
     */
    function getPlugin( $name )
    {
        if( isset( $this->plugins[ $name ] ) )
            return $this->plugins[ $name ];
    }


    /**
     * load plugin
     */
    function load( $name , $config = array() )
    {
        # $name = '\\' . ltrim( $name , '\\' );
        $class = "\\$name\\$name";
        $plugin = $class::getInstance();
        $plugin->setConfig( $config );
        $plugin->init();
        return $this->plugins[ $name ] = $plugin;
    }

}

