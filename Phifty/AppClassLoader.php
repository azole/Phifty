<?php
namespace Phifty;
use Phifty\AbstractClassLoader;
use Phifty\FileUtils;
use Phifty\Singleton;

class AppClassLoader extends Singleton
{

    /**
     * nsPath
     * 
     * @var array
     */
    public $nsPaths = array( );

    // xxx: 
    // Move Collection loader out, remove this redundant classloader
    // Use General classloader
    public $supportedTypes = array( 
        'Model' => 1,
        'Controller' => 1,
        'Action' => 1,
        'View' => 1
    );

    function add( $ns, $path )
    {
        $this->nsPaths[ $ns ] = $path;
    }

    /**
     * To load core app class, app class, and plugins
     *
     * Register paths:
     *
     *  'Core' => PH_ROOT ,
     *  'App'  => PH_APP_ROOT,
     *  'AdminUI' => 'plugins/AdminUI',  # \AdminUI
     *
     *  @param string $class
     */
    function load( $class )
    {
        // get first part of ns name
        $parts = explode('\\', $class,3);
        $ns = $parts[0];

        /**
         * check if the namespace is registered.
         */
        if( isset($this->nsPaths[ $ns ] ) ) {
            foreach( $this->nsPaths[ $ns ] as $path ) {
                $classPath = $path . '/' . str_replace( array('\\') , DIRECTORY_SEPARATOR , $class ) . '.php';
                if( file_exists($classPath) ) {
                    require $classPath;
                    return true;
                }
            }
        }
    }

    function register() 
    {
        spl_autoload_register( array( $this, "load" ),
            false, // throw
            false // prepend 
        );
    }

}
