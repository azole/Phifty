<?php
namespace Phifty {
use Phifty\Kernel;
use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;

/**
 * Script for phifty kernel bootstrap
 *
 * load config file and bootstrap
 * and export $kernel to global.
 *
 * @author c9s <cornelius.howl@gmail.com>
 */
class Bootstrap
{

    public static function initConstants()
    {
    }

    public static function initClassLoader()
    {
        $loader = null;
        if ( extension_loaded('apc') ) {
            require PH_ROOT . '/vendor/corneltek/universal/src/Universal/ClassLoader/ApcClassLoader.php';
            $loader = new \Universal\ClassLoader\ApcClassLoader( PH_ROOT );
        } else {
            $loader = new \Universal\ClassLoader\SplClassLoader;
        }

        // create spl classloader
        $loader->addNamespace(array(
            'Phifty'         => PH_ROOT . '/src',
            'ActionKit'      => PH_ROOT . '/src',
            'I18NKit'        => PH_ROOT . '/src',
            'Kendo'          => PH_ROOT . '/src',
            'GenPHP'         => PH_ROOT . '/vendor/genphp/src',
        ));
        $loader->addFallback( PH_ROOT . '/vendor/pear' );
        $loader->useIncludePath(true);
        $loader->register();
        return $loader;
    }

    public static function initConfigLoader()
    {
        // We load other services from the definitions in config file
        // Simple load three config files (framework.yml, database.yml, application.yml)

        $loader = new ConfigLoader;
        if ( file_exists( PH_APP_ROOT . '/config/framework.yml') )
            $loader->load('framework', PH_APP_ROOT . '/config/framework.yml');

        // This is for DatabaseService
        if ( file_exists( PH_APP_ROOT . '/db/config/database.yml') ) {
            $loader->load('database', PH_APP_ROOT . '/db/config/database.yml');
        } elseif ( file_exists( PH_APP_ROOT . '/config/database.yml') ) {
            $loader->load('database', PH_APP_ROOT . '/config/database.yml');
        }

        // Config for application, services does not depends on this config file.
        if ( file_exists( PH_APP_ROOT . '/config/application.yml') )
            $loader->load('application', PH_APP_ROOT . '/config/application.yml');

        // Only load testing configuration when environment
        // is 'testing'
        if ( getenv('PHIFTY_ENV') === 'testing' ) {
            if ( file_exists( PH_APP_ROOT . '/config/testing.yml' ) ) {
                $loader->load('testing', ConfigCompiler::compile(PH_APP_ROOT . '/config/testing.yml') );
            }
        }

        return $loader;
    }

    /**
     * Load important services by sequence:
     *
     * 1. ClassLoaderService
     * 2. ConfigService
     * 3. DatabaseService
     *
     * @param Phifty\Kernel $kernel      kernel object.
     * @param ClassLoader   $classloader
     */
    public static function bootKernel($kernel,$classloader)
    {
        // register default classloader service
        $kernel->registerService(
            new \Phifty\Service\ClassLoaderService($classloader)
        );

        $configLoader = self::initConfigLoader();
        $configService = new \Phifty\Service\ConfigService($configLoader);

        // load config service first.
        $kernel->registerService( $configService );

        // load a event service, so that we can bind events in Phifty
        // currently we are working on a CTEvent extension, which provides a better
        // performance than pure PHP class.
        $kernel->registerService( new \Phifty\Service\EventService );

        // if the framework config is defined.
        if ( $configLoader->isLoaded('framework') ) {
            // we should load database service before other services
            // because other services might need database service
            if ( $configLoader->isLoaded('database') ) {
                $kernel->registerService( new \Phifty\Service\DatabaseService );
            }

            if ( $appconfigs = $kernel->config->get('framework','Applications') ) {
                foreach ($appconfigs as $appname => $appconfig) {
                    $kernel->classloader->addNamespace( array(
                        $appname => array( PH_APP_ROOT . '/applications' , PH_ROOT . '/applications' )
                    ));
                }
            }

            if ( $services = $kernel->config->get('framework','Services') ) {
                foreach ($services as $name => $options) {
                    // not full qualified classname
                    $class = ( false === strpos($name,'\\') ) ? ('Phifty\\Service\\' . $name) : $name;
                    $kernel->registerService( new $class , $options );
                }
            }
        }
    }

    /**
     * Create Kernel object with environment type, production or development
     *
     * @param string $env Environment type
     */
    public static function createKernel($env = null)
    {
        return new Kernel( $env );
    }
}

}
namespace {
    defined( 'PH_ROOT' )     || define( 'PH_ROOT' , dirname(dirname(__DIR__)) );
    defined( 'PH_APP_ROOT' ) || define( 'PH_APP_ROOT' , PH_ROOT );
    defined( 'DS' )          || define( 'DS' , DIRECTORY_SEPARATOR );

    // ObjectContainer is required by Kernel
    require PH_APP_ROOT . '/vendor/autoload.php';
    require PH_ROOT . '/vendor/corneltek/universal/src/Universal/ClassLoader/SplClassLoader.php';
    require PH_ROOT . '/vendor/corneltek/universal/src/Universal/Container/ObjectContainer.php';

    // Load Kernel so we don't need to load by classloader.
    if ( ! class_exists('ConfigKit\ConfigLoader') ) {
        require PH_ROOT . '/vendor/pear/ConfigKit/ConfigCompiler.php';
        require PH_ROOT . '/vendor/pear/ConfigKit/Accessor.php';
        require PH_ROOT . '/vendor/pear/ConfigKit/ConfigLoader.php';
    }
    require PH_ROOT . '/src/Phifty/Kernel.php';

    use Phifty\Bootstrap;

    global $kernel;

    /**
     * kernel() is a global shorter helper function to get Phifty\Kernel instance.
     *
     * Initialize kernel instance, classloader, plugins and services.
     *
     * @return Phifty\Kernel
     */
    function kernel()
    {
        global $kernel;
        if ( $kernel )

            return $kernel;

        $classloader = Bootstrap::initClassLoader();
        $kernel      = Bootstrap::createKernel();
        Bootstrap::bootKernel($kernel,$classloader);
        $kernel->init();

        return $kernel;
    }

    // bootstrap here
    kernel();
}
