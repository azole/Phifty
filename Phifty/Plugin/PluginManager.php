<?php
namespace Phifty\Plugin;
use Exception;
use Phifty\FileUtils;
use Phifty\Singleton;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class PluginManager
    implements ArrayAccess, IteratorAggregate
{

    /**
     * plugin object stack
     */
    public $plugins = array();

    public function isLoaded( $name )
    {
        return isset( $this->plugins[ $name ] );
    }

    public function getList()
    {
        return array_keys( $this->plugins );
    }

    public function getPlugins()
    {
        return array_values( $this->plugins );
    }

    /**
     * has plugin 
     */
    public function has( $name )
    {
        return isset($this->plugins[ $name ]);
    }


    /**
     * get plugin object
     */
    public function get( $name )
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
        if( class_exists($class,true) ) {
            $plugin = $class::getInstance();

            // xxx: better solution
            $plugin->mergeWithDefaultConfig( $config );
            $plugin->init();
            return $this->plugins[ $name ] = $plugin;
        }
        throw new Exception("Plugin $class is not found.");
        return false;
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

    static function getInstance() {
        static $instance;
        return $instance ?: $instance = new static;
    }

}

