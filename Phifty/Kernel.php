<?php
namespace Phifty;

use Phifty\Kernel;
use Phifty\CurrentUser;
use Phifty\L10N;
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

    /* rootDir: contains app, web, phifty dirs */
    public $rootDir; 

    /* phifty dir */
    public $frameworkDir;

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

    public function __construct( $environment = null ) 
    {
        /* define framework environment */
        $this->environment  = $environment ?: getenv('PHIFTY_ENV') ?: 'development';

        // path info
        $this->frameworkDir = PH_ROOT; // Kernel is placed under framework directory
        $this->rootDir      = PH_APP_ROOT; // Application root.
        $this->appDir       = PH_APP_ROOT . DS . 'applications';
        $this->webroot      = PH_APP_ROOT . DS . 'webroot';
    }

    public function registerService( ServiceInterface $service )
    {
        $service->register( $this );
    }

    public function init()
    {
        $this->event->trigger('phifty.before_init');
        $this->isCLI        = isset($_SERVER['argc']);
        $self = $this;


        $this->currentUser = function() use ($self) {
            $currentUserClass = $self->config->get('application','current_user.class');
            return new $currentUserClass;
        };

        $this->web = function() use($self) { 
            return new \Phifty\Web( $self );
        };

        /**
         * detect for development mode 
         */
        $this->isDev = $this->environment == 'development';

        // Turn off all error reporting
        if( $this->isDev || $this->isCLI ) {
            \Phifty\Environment\Development::init($this);
        }
        else {
            \Phifty\Environment\Production::init($this);
        }


        $appconfigs = $this->config->get('framework','apps');
        foreach( $appconfigs as $appname => $appconfig ) {
            $this->classloader->addNamespace( array( 
                $appname => array( 
                    PH_APP_ROOT . '/applications' , PH_ROOT . '/applications' 
                )
            ));
            $this->loadApp( $appname , $appconfig );
        }

        /*
        $pluginConfigs = $this->config->get('framework','plugins');
        if( $pluginConfigs ) {
            foreach( $pluginConfigs as $name => $config ) {
                $loader->add( $name , array( PH_APP_ROOT . '/plugins' , PH_ROOT . '/plugins' ) );
            }
        }
         */
        define( 'CLI_MODE' , $this->isCLI );

        if( $this->isCLI ) {
            ini_set('output_buffering ', '0');
            ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
        } else {
            ob_start();
            $s = $this->session; // build session object
            mb_internal_encoding('UTF-8');
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
     * get current application name
     */
    public function getAppName()
    {
        return $this->appName;
    }


    /** 
     * application namespace
     */
    public function getAppNs()
    {
        return $this->appNs;
    }

    public function getAppPluginDir()
    {
        return $this->rootDir . DS . 'plugins';
    }

    public function getFrameworkBundleDir()
    {
        return $this->frameworkDir . DS . PHIFTY_APP_DIRNAME;
    }

    public function getCoreDir()
    {
        return $this->getFrameworkBundleDir() . DS . 'Core';
    }


    /* we should move this into bundles dir */
    public function getFrameworkPluginDir()
    {
        return $this->frameworkDir . DS . 'plugins';
    }

    public function getMinifiedWebDir()
    {
        return $this->rootDir . DS . PHIFTY_WEBROOT_DIRNAME . DS . 'static' . DS . 'minified';
    }

    public function getCoreWebDir()
    {
        return $this->getCoreDir() . DS . 'web';
    }

    public function getWebRootDir()
    {
        return $this->rootDir . DS . PHIFTY_WEBROOT_DIRNAME;
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
        return $this->rootDir . DS . PHIFTY_WEBROOT_DIRNAME .  DS . 'ph' . DS . 'plugins';
    }


    /*
     * Get exported widget web dir
     *
     *     widgets/Foo/web => webroot/ph/widgets/Foo
     *
    */
    public function getWebAssetDir()
    {
        return $this->rootDir . DS . PHIFTY_WEBROOT_DIRNAME . DS . 'ph' . DS . 'assets';
    }


    /**
     * Get Root Dir
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }


    /**
     * return framework id
     */
    public function getFrameworkId()
    {
        return 'phifty';
    }

    public function getFrameworkDir()
    {
        return $this->frameworkDir;
    }



    /** 
     * Locale Related 
     */
    public function currentLocale()
    {
        return $this->locale->speaking();
    }

    public function currentLang()
    {
        return $this->currentLocale();
    }

    /* return Phifty\L10N */
    public function lang()
    {
        return $this->locale;
    }

    public function run() 
    {
        $this->event->trigger('phifty.before_run');

        // check if there is $_POST['action'] or $_GET['action']
        if( isset($_POST) || isset( $_GET ) || isset( $_FILES ) ) {

            // only run action in POST,GET method
            $runner = ActionRunner::getInstance();
            try 
            {
                $result = $runner->run();
                if( $result && $runner->isAjax() ) {
                    echo $result;
                    exit(0);
                }
            } 
            catch( Exception $e ) 
            {
                /**
                 * return 403 status forbidden
                 */
                header('HTTP/1.0 403');
                if( $runner->isAjax() ) {
                    die( json_encode( array( 'error' => $e->getMessage() ) ) );
                } else {
                    die( $e->getMessage() );
                }
            }
        }
        $this->event->trigger('phifty.after_run');
    }


    /**
     * backward-compatible
     */
    public function getPlugin($name) 
    {
        return $this->plugin->getPlugin( $name );
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

