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
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class PluginManager extends Singleton
    implements ArrayAccess, IteratorAggregate
{

    /**
     * plugin object stack
     */
    public $plugins = array();


    
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
     * has plugin 
     */
	function hasPlugin( $name )
	{
		return isset($this->plugins[ $name ]);
	}


    /**
     * get plugin object
     */
    function get( $name )
    {
        if( isset( $this->plugins[ $name ] ) )
            return $this->plugins[ $name ];
    }


    /**
     * Load plugin
     */
    public function load( $name , $config = array() )
    {
        # $name = '\\' . ltrim( $name , '\\' );
        $class = "\\$name\\$name";
        $plugin = $class::getInstance();
        $plugin->mergeWithDefaultConfig( $config );
        $plugin->init();
        return $this->plugins[ $name ] = $plugin;
    }

    public function add($name,$plugin) 
    {
        $this->plugins[ $name ] = $plugin;
    }
    
    public function offsetSet($name,$value)
    {
        $this->plugins[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->plugins[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->plugins[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->plugins[$name]);
    }

    public function getIterator() 
    {
        return new ArrayIterator( $this->plugins );
    }




}

