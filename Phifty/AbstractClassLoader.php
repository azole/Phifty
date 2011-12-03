<?php
namespace Phifty;

abstract class AbstractClassLoader 
{

    public $throw = false;
    public $prepend = false;

    function __construct( $config = null ) 
    {
        if( $config ) {
            $this->throw = @$config['throw'];
            $this->prepend = @$config['prepend'];
        }
    }

    static function getInstance()
    {
        static $object;
        $class = get_called_class();
        return $object ? $object : $object = new $class;
    }

    public function lookup( $lookups )
    {
        foreach( $lookups as $path ) {
            if( file_exists( $path ) ) {
                return $path;
            }
        }
    }

    public function requireFile( $file )
    {
        include $file;
        return true;
    }

    public function tryRequire( $path )
    {
        if( file_exists($path) ) {
            require $path;
            return true;
        }
        return false;
    }

    public function loadClass($class) 
    {
        $file = $this->locateClass( $class );
        if( $file )
            return $this->requireFile( $file );
    }

    public function register() 
    {
        spl_autoload_register( array( $this, "loadClass" ) , $this->throw , $this->prepend );
    }

}


