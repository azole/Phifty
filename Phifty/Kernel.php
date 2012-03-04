<?php
namespace Phifty;
require PH_ROOT . '/src/Phifty/ConfigLoader.php';
require PH_ROOT . '/src/Phifty/AppClassKit.php';
require PH_ROOT . '/src/Phifty/AppClassLoader.php';
require PH_ROOT . '/src/Phifty/CurrentUser.php';
require PH_ROOT . '/src/Phifty/L10N.php';
require PH_ROOT . '/src/Phifty/FileUtils.php';

use Phifty\Kernel;
use Phifty\CurrentUser;
use Phifty\L10N;
use Phifty\Web;
use Phifty\AppClassLoader;
use Phifty\AppClassKit;
use Phifty\FileUtils;
use Phifty\Action\ActionRunner;
use Universal\Container\ObjectContainer;
use CacheKit\CacheKit;
use CacheKit\ApcCache;
use Roller\Router;
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
    const VERSION = '2.2';

    /* rootDir: contains app, web, phifty dirs */
    public $rootDir; 

    /* phifty dir */
    public $frameworkDir;

    /* app name */
    public $appName;

    /* app name (lower case) */
    public $appId;

    /* boolean: is in command mode ? */
    public $isCLI;

    /* boolean: is in development mode ? */
    public $isDev = true;

    public $apps = array();

    public $environment = 'dev';

    public function __construct( $environment = null ) 
    {
        $this->frameworkDir = PH_ROOT; // Kernel is placed under framework directory
        $this->rootDir      = PH_APP_ROOT;
        $this->appName      = PH_APP_NAME;
        $this->environment  = $environment 
                ?: getenv('PHIFTY_ENV') 
                ?: (isset($_REQUEST['PHIFTY_ENV']) 
                    ? $_REQUEST['PHIFTY_ENV'] : 'dev');

        
        $this->appId        = strtolower( PH_APP_NAME );
        $this->isCLI        = isset($_SERVER['argc']);
        $self = $this;

        $this->configLoader = function() use ($self) {
            return new \Phifty\ConfigLoader( $self );
        };

        $this->locale  = function() {
            return new L10N;
        };

        $this->apc = function() use ($self) {
            return new ApcCache( $self->appName );
        };

        $this->session = function() use ($self) {
            $session = new \Universal\Session\Session(array(  
                'state'   => new \Universal\Session\State\NativeState,
                'storage' => new \Universal\Session\Storage\NativeStorage,
            ));
            return $session;
        };

        // php event pool
        $this->event = function() {
            return new \Universal\Event\PhpEvent;
        };

        $this->cache = function() use ($self) {
            $b = array();
            if( extension_loaded('apc') )
                $b[] = $self->apc;
            /*
            if( extension_loaded('memcache') )
                $b[] = new \CacheKit\MemcacheCache( array( array('localhost',11211) ) );
            */
            return new CacheKit($b);
        };

        $this->router = function() {
            return new Router(null, array( 
                'route_class' => 'Phifty\Routing\Route',
                'cache_id' => PH_APP_NAME,
            ));
        };

        $this->currentUser = function() use ($self) {
            $currentUserClass = $self->config('current_user.class');
            return new $currentUserClass;
        };

        $loader = \Lazy\ConfigLoader::getInstance();
        if( ! $loader->loaded ) { 
            $loader->load( PH_APP_ROOT . '/.lazy.php');
            $loader->init();  // init datasource and connection
        }

        $this->db = function() use($self) {
            $conm = \Lazy\ConnectionManager::getInstance();
            return $conm->getConnection();
        };

        $this->web = function() use($self) { 
            return new \Phifty\Web( $self );
        };

        $this->plugin = function() {
            return \Phifty\PluginManager::getInstance();
        };

        $this->mailer = function() {
            require_once __DIR__ . '/vendor/pear/swift_required.php';

            // Mail transport
            $transport = Swift_MailTransport::newInstance();

            // Create the Mailer using your created Transport
            return Swift_Mailer::newInstance($transport);
        };

        /**
         * detect for development mode 
         */
        $this->isDev = $this->environment == 'dev';

        // Turn off all error reporting
        if( $this->isDev || $this->isCLI ) {
            \Phifty\Environment\Development::init($this);
        }
        else {
            \Phifty\Environment\Production::init($this);
        }
    }

    public function init()
    {
        $this->event->trigger('phifty.before_init');
        $this->initAppClassLoader();


        define( 'CLI_MODE' , $this->isCLI );

        if( $this->isCLI ) {
            ini_set('output_buffering ', '0');
            ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
        } else {
            ob_start();
            $s = $this->session; // build session object
            mb_internal_encoding("UTF-8");
        }




        $this->initLang();

        $this->initPlugins();

        $this->event->trigger('phifty.after_init');

    }

    public function loadApp( $appName ) 
    {
        $class = $appName . '\Application';
        $app = $class::getInstance();
        return $this->apps[ $appName ] = $app;
    }


    /*
    public function getApp( $appName )
    {
        if( isset($this->apps[ $appName ]) )
            return $this->apps[ $appName ];
    }
    */

    public function isCLI()
    {
        return $this->isCLI;
    }

    public function getAppClass( $bundle )
    {
        return '\\' . $bundle . '\\Application';
    }

    public function getApp($bundleName)
    {
        $class = $this->getAppClass($bundleName);
        $instance = $class::getInstance();
        return $instance;
    }


    /**
     * get current application name
     */
    public function getAppName()
    {
        return $this->appName;
    }


    public function getAppId()
    {
        return $this->appId;
    }


    public function getAppDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_APP_DIRNAME . DIR_SEP . $this->appName;
    }


    public function getAppPluginDir()
    {
        return $this->rootDir . DIR_SEP . 'plugins';
    }

    public function getFrameworkBundleDir()
    {
        return $this->frameworkDir . DIR_SEP . PHIFTY_APP_DIRNAME;
    }

    public function getCoreDir()
    {
        return $this->getFrameworkBundleDir() . DIR_SEP . 'Core';
    }


    /* we should move this into bundles dir */
    public function getFrameworkPluginDir()
    {
        return $this->frameworkDir . DIR_SEP . 'plugins';
    }

    public function getMinifiedWebDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME . DIR_SEP . 'static' . DIR_SEP . 'minified';
    }

    public function getAppWebDir()
    {
        return $this->rootDir  . DIR_SEP . PHIFTY_APP_DIRNAME . DIR_SEP . $this->appName . DIR_SEP . 'web';
    }

    public function getCoreWebDir()
    {
        return $this->getCoreDir() . DIR_SEP . 'web';
    }

    public function getWebRootDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME;
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
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME .  DIR_SEP . 'ph' . DIR_SEP . 'plugins';
    }


    /*
     * Get exported widget web dir
     *
     *     widgets/Foo/web => webroot/ph/widgets/Foo
     *
    */
    public function getWebAssetDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME . DIR_SEP . 'ph' . DIR_SEP . 'assets';
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

    public function initLang()
    {
        $l10n = $this->locale;
        $i18nConfig = $this->config('i18n');

        // var_dump( $i18nConfig ); 
        // var_dump( $_SESSION ); 
        $l10n->setDefault( $i18nConfig->default );
        $l10n->domain( $this->appId ); # use application id for domain name.

        $localeDir = $this->getRootDir() . DIRECTORY_SEPARATOR . $i18nConfig->localedir;

        $l10n->localedir( $localeDir );

        /* add languages to list */
        foreach( @$i18nConfig->lang as $localeName ) {
            $l10n->add( $localeName );
        }

        $l10n->init();

        # _('en');
    }

    public function pluginList()
    {
        $config = (array) $this->config( 'plugins' );
        if( ! $config )
            return array();
        return array_keys( $config );
    }

    public function initAppClassLoader() 
    {
        // app names
        $pluginConfigs = $this->config( 'plugins' );
        $loader = AppClassLoader::getInstance();
        $loader->register();

        // get application list from config.
        $apps = $this->config('apps');
        foreach( $apps as $appName => $root ) {
            $loader->add( $appName , (array) (PH_APP_ROOT . DIRECTORY_SEPARATOR . $root) );
            $appClass = $this->getAppClass( $appName );
            $appClass::getInstance()->init();
        }

        /**
         * also mount plugin dir to path 
         */
        if( $pluginConfigs ) {
            foreach( $pluginConfigs as $name => $config ) {
                $loader->add( $name , array( PH_APP_ROOT . '/plugins' , PH_ROOT . '/plugins' ) );
            }
        }
    }

    public function initPlugins()
    {
        $pluginConfigs = $this->config( 'plugins' );
        if( ! $pluginConfigs )
            return;
        $this->plugin->loadFromList( $pluginConfigs );
    }

    public function hasPlugin($name) 
    {
        return $this->plugin->hasPlugin( $name );
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
     * @return: if $key is empty, return the config hash, 
     *          or will return the config value.
     */
    public function config($key = null , $default = null ) 
    {
        if( $key ) {
            $val = $this->configLoader->get( $key );
            return $val ? $val : $default;
        }
        return $this->configLoader->getConfig();
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

