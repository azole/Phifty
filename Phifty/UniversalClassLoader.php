<?php
namespace Phifty;

class UniversalClassLoader extends \Phifty\AbstractClassLoader 
{
    var $namespaces = array();
    var $prefixes   = array();
    var $fallbacks = array();

    var $throw = true;
    var $prepend = true;

    public function __construct() 
    {
    }

    public function addNSPrefix( $prefixes )
    {
        $this->prefixes = $prefixes;
    }

    public function addNS( $namespaces ) 
    {
        # type convention
        foreach( $namespaces as $ns => $paths )
            $this->namespaces[ $ns ] = (array) $paths;
    }

    /*
     * return namespaces
     *
     * @return array hash
     * */
    public function getNamespaces() 
    {
        return $this->namespaces;
    }

    public function loadClass($class)
    {
        $class = ltrim($class, '\\');
        $cache = null;
        if( strpos( $class , '_' ) !== false ) {
            // try to load prefix
            foreach( $this->prefixes as $prefix => $dirs ) 
            {
                foreach( (array) $dirs as $dir ) 
                {
                    if( file_exists($file = $dir . DIRECTORY_SEPARATOR . 
                        str_replace( '_' , '/' , $class ) . '.php' ) ) 
                    {
                        require $file;
                        return true;
                    }
                }
            }
        }

        $classNs = strstr( $class , '\\' , true );
        $class   = str_replace( '\\' , DIRECTORY_SEPARATOR , $class );
        $classFn = DIRECTORY_SEPARATOR . $class . ".php";

        if( isset($this->namespaces[ $classNs ]) ) {
            $dirs = $this->namespaces[ $classNs ];
            foreach( $dirs as $dir ) {
                $path = $dir . $classFn;
                if( $this->tryRequire( $path ) ) {
                    return true;
                }
            }
        }

        foreach( $this->namespaces as $ns => $dirs ) {
            foreach( $dirs as $dir ) {
                $path = $dir . $classFn;
                if( $this->tryRequire( $path ) )  {
                    return true;
                }
            }
        }
        foreach( $this->fallbacks as $dir ) {
            $path = $dir . $classFn;
            if( $this->tryRequire( $path ) ) {
                return true;
            }
        }
    }
}

