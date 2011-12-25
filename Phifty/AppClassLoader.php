<?php
namespace Phifty;
use Phifty\AbstractClassLoader;
use Phifty\PluginPool;
use Phifty\FileUtils;
use Phifty\Singleton;

class AppClassLoader extends Singleton
{
    public $nsPaths = array( );

    // xxx: Move Collection loader out, remove this redundant classloader

    public $supportedTypes = array( 
        'Model' => 1,
        'Controller' => 1,
        'Action' => 1,
        'View' => 1
    );

    function __construct() 
    {

    }

    function add( $ns, $path )
    {
        $this->nsPaths[ $ns ] = $path;
    }

    /**
     *
     * To load core app class, app class, and plugins
     *
     * Core/Core.php             <= use Core\Core;
     * Core/Controller/ ...      <= use Core\Controller\...;
     * Core/Action/ ...          <= use Core\Action\Create....;
     *
     * App/App.php               <= use App\App;
     * App/Controller/Index.php  <= use App\Controller\Index;
     *
     * plugins/AdminUI/AdminUI.php
     * plugins/AdminUI/Controller/Login.php
     *
     *
     * Register paths:
     *
     *      'Core' => PH_ROOT ,
     *      'App'  => PH_APP_ROOT,
     *      'AdminUI' => 'plugins/AdminUI',  # \AdminUI
     *
     */

    // if class name contains 'model','collection','template' ... etc
    // use ns root path   
    //      PH_ROOT/{ns}/{type}/{rest}.php
    //
    // if not,
    // use root path + 'src' + ns path
    //
    //      PH_ROOT/{ns}/src/{ns}/class.php
    function load( $class )
    {
        // get first part of ns name
        $parts = explode('\\', $class,3);
        $ns = $parts[0];

        if( isset($this->nsPaths[ $ns ] ) ) {
            $paths = $this->nsPaths[ $ns ];
            
            foreach( $paths as $path ) {
                $classPath = $path . '/' . str_replace( array('\\') , DIRECTORY_SEPARATOR , $class ) . '.php';

                if( file_exists($classPath) ) {
                    require $classPath;
                    return true;
                }

                /* If it's supported types, the class name should have 3 parts. */
                if( count($parts) > 2 ) {
                    // special case for collection class.
                    if( substr( $class , -10 ) == 'Collection' ) {

                        // if so, load model first.
                        $modelClass = substr( $class, 0, -10 );
                        if( ! class_exists( $modelClass ) ) {
                            $this->load( $modelClass );
                        }

                        if( ! class_exists( $modelClass ) ) {
                            $modelClass .= 'Model';
                            if( ! class_exists( $modelClass ) )
                                $this->load( $modelClass );
                        }

                        if( class_exists( $modelClass ) )
                            return $modelClass::produceCollectionClass();
                    }
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

