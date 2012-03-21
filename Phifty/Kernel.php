<?php
namespace Phifty;

use Phifty\Kernel;
use Phifty\CurrentUser;
use Phifty\Locale;
use Phifty\Web;
use Phifty\FileUtils;
use Phifty\Action\ActionRunner;
use Universal\Container\ObjectContainer;
use Phifty\Service\ServiceInterface;
use Exception;

/*
    Phifty Main Controll Object:
        process startup operations

        init header
        init db
        init language
        init core object
        init plugin objects
        init app object

        run actions

        dispatch controllers
*/

class Kernel extends ObjectContainer 
{
    /* framework version */
    const VERSION = '2.3.0';
    const FRAMEWORK_ID = 'phifty';

    public $frameworkDir;
    public $frameworkAppDir;
    public $frameworkPluginDir;

    public $rootDir;  // application root dir
    public $rootAppDir;   // application dir (./applications)
    public $rootPluginDir;


    /* application namespace */
    public $namespace;

    /* application uuid */
    public $uuid;

    /* boolean: is in command mode ? */
    public $isCLI;

    /* boolean: is in development mode ? */
    public $isDev = true;

    /**
     * application object pool
     *
     * app class name => app object
     */
    public $applications = array();

    public $environment = 'development';

    public $services = array();

    public function __construct( $environment = null ) 
    {
        /* define framework environment */
        $this->environment  = $environment ?: getenv('PHIFTY_ENV') ?: 'development';
        $this->isCLI        = isset($_SERVER['argc']);
        // detect development mode 
        $this->isDev = $this->environment == 'development';

        // build path info
        $this->frameworkDir       = PH_ROOT;
        $this->frameworkAppDir    = PH_ROOT . DS . 'applications';
        $this->frameworkPluginDir = PH_ROOT . DS . 'plugins';
        $this->rootDir            = PH_APP_ROOT;      // Application root.
        $this->rootAppDir         = PH_APP_ROOT . DS . 'applications';
        $this->rootPluginDir      = PH_APP_ROOT . DS . 'plugins';
        $this->webroot            = PH_APP_ROOT . DS . 'webroot';
    }

    public function registerService( ServiceInterface $service, $options = array() )
    {
        $service->register( $this , $options );
        $this->services[ $service->getId() ] = $service;
    }

    public function init()
    {
        $this->event->trigger('phifty.before_init');

        $self = $this;

        $this->web = function() use($self) { 
            return new \Phifty\Web( $self );
        };

        // Turn off all error reporting
        if( $this->isDev || $this->isCLI ) {
            \Phifty\Environment\Development::init($this);
        }
        else {
            \Phifty\Environment\Production::init($this);
        }

        if( $this->isCLI ) {
            \Phifty\Environment\CommandLine::init($this);
        }
        else {
            // build session
            $this->session;
            $this->locale;
        }

        $appconfigs = $this->config->get('framework','Applications');
        if( $appconfigs ) {
            foreach( $appconfigs as $appname => $appconfig ) {
                $this->classloader->addNamespace( array( 
                    $appname => array( 
                        PH_APP_ROOT . '/applications' , PH_ROOT . '/applications' 
                    )
                ));
                $this->loadApp( $appname , $appconfig );
            }
        }

        $this->event->trigger('phifty.after_init');
    }


    /**
     * Create application object
     */
    public function loadApp($appname, $config = array() ) 
    {
        $class = $appname . '\Application';
        $app = $class::getInstance();
        $app->config = $config;
        $app->init();
        return $this->applications[ $appname ] = $app;
    }


    /**
     * Get application object
     *
     * @param string application name
     *
     * @code
     *
     *   kernel()->app('Core')->getController('ControllerClass');
     *   kernel()->app('Core')->getModel('ModelClass');
     *   kernel()->app('Core')->getNamespace();
     *   kernel()->app('Core')->locate();
     *
     * @endcode
     */
    public function app( $appname )
    {
        if( isset($this->applications[ $appname ]) )
            return $this->applications[ $appname ];
    }



    /**
     * get service object
     */
    public function service($id) 
    {
        if( isset($this->services[ $id ] ) )
            return $this->services[ $id ];
    }

    /**
     * Get plugin object from plugin service
     *
     * backward-compatible
     */
    public function plugin($name) 
    {
        return $this->plugins->get( $name );
    }


    /**
     * Get current application name from config
     */
    public function getApplicationName()
    {
        return $this->config->framework->ApplicationName;
    }


    /**
     * Get application UUID from config
     */
    public function getApplicationUUID()
    {
        return $this->config->framework->ApplicationUUID;
    }




    public function getMinifiedWebDir()
    {
        return $this->webroot . DS . 'static' . DS . 'minified';
    }


    /**
     * Get exported plugin webdir
     * 
     * web dir structure
     *
     *   web/ph/plugins/sb/
     *   web/ph/plugins/product/
     *   web/ph/plugins/coupon/
     *   ..... etc
     * */
    public function getWebPluginDir()
    {
        return $this->webroot .  DS . 'ph' . DS . 'plugins';
    }

    /**
     * Get exported widget web dir
     *
     *     widgets/Foo/web => webroot/ph/widgets/Foo
     *
     */
    public function getWebAssetDir()
    {
        return $this->webroot . DS . 'ph' . DS . 'assets';
    }



    /**
     * return framework id
     */
    public function getFrameworkId()
    {
        return self::FRAMEWORK_ID;
    }

    public function run() 
    {
        $this->event->trigger('phifty.before_run');
        $this->event->trigger('phifty.run');
        $this->event->trigger('phifty.after_run');
    }


    /**
     * get Phifty\Web object
     */
    public function web()
    {
        return $this->web;
    }

    /**
     * get Template Engine
     **/
    public function view()
    {
        return new \Phifty\View;
    }

	public function __toString()
	{
		return '<pre>' . get_class($this ) . '</pre>';
   	}


    static function getInstance()
    {
        static $one;
        if( $one )
            return $one;
        return $one = new static;
    }

}

