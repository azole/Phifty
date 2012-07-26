<?php
namespace Phifty {
use Phifty\Kernel;
use Universal\ClassLoader\SplClassLoader;
use Universal\ClassLoader\ApcClassLoader;

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

    static function initConstants() {
        defined( 'PH_ROOT' )     || define( 'PH_ROOT' , dirname(dirname(__DIR__)) );
        defined( 'PH_APP_ROOT' ) || define( 'PH_APP_ROOT' , PH_ROOT );
        defined( 'DS' )          || define( 'DS' , DIRECTORY_SEPARATOR );
    }

    static function initClassLoader() 
    {
        $loader = null;
        if( extension_loaded('apc') ) {
            require PH_ROOT . '/vendor/universal/src/Universal/ClassLoader/ApcClassLoader.php';
            $loader = new ApcClassLoader( PH_ROOT );
        } else {
            require PH_ROOT . '/vendor/universal/src/Universal/ClassLoader/SplClassLoader.php';
            $loader = new SplClassLoader;
        }

        // create spl classloader
        $loader->addNamespace(array( 
            'Phifty'     => PH_ROOT . '/src',
            'ActionKit'  => PH_ROOT . '/src',
            'I18NKit'    => PH_ROOT . '/src',
            'Universal'  => PH_ROOT . '/vendor/universal/src',
            'SQLBuilder' => PH_ROOT . '/vendor/sqlbuilder/src',
            'AssetKit'   => PH_ROOT . '/vendor/assetkit/src',
            'LazyRecord' => PH_ROOT . '/vendor/lazyrecord/src',
            'FormKit'    => PH_ROOT . '/vendor/formkit/src',
            'Roller'     => PH_ROOT . '/vendor/roller/src',
        ));
        $loader->addFallback( PH_ROOT . '/vendor/pear' );
        $loader->useIncludePath(true);
        $loader->register();
        return $loader;
    }

    static function initConfigLoader() 
    {
        // We load other services from the definitions in config file
        // Simple load three config files (framework.yml, database.yml, application.yml)

        $loader = new \Phifty\Config\ConfigLoader;
        if( file_exists( PH_APP_ROOT . '/config/framework.php') )
            $loader->load('framework', PH_APP_ROOT . '/config/framework.php');

        // This is for DatabaseService
        if( file_exists( PH_APP_ROOT . '/config/database.php') ) {
            $loader->load('database', PH_APP_ROOT . '/config/database.php');
        }

        // Only load testing configuration when environment 
        // is 'testing'
        if( getenv('PHIFTY_ENV') === 'testing' ) {
            if( file_exists( PH_APP_ROOT . '/config/testing.php' ) ) {
                $loader->load('testing', PH_APP_ROOT . '/config/testing.php' );
            }
        }

        // Config for application, services does not depends on this config file.
        if( file_exists( PH_APP_ROOT . '/config/application.php') )
            $loader->load('application', PH_APP_ROOT . '/config/application.php' );
        return $loader;
    }

    static function bootKernel($kernel,$classloader) {

        $classloaderService = new \Phifty\Service\ClassLoaderService;
        $classloaderService->setClassLoader($classloader);
        $kernel->registerService( $classloaderService );

        $configLoader = self::initConfigLoader();
        $configService = new \Phifty\Service\ConfigService($configLoader);

        // load config service first.
        $kernel->registerService( $configService );

        // load a event service, so that we can bind events in Phifty
        // currently we are working on a CTEvent extension, which provides a better 
        // performance than pure PHP class.
        $kernel->registerService( new \Phifty\Service\EventService );

        // if the framework config is defined.
        if( $configLoader->isLoaded('framework') ) {
            if( $services = $kernel->config->get('framework','Services') ) {
                foreach( $services as $name => $options ) {
                    // not full qualified classname
                    $class = ( false === strpos($name,'\\') ) ? ('Phifty\\Service\\' . $name) : $name;
                    $kernel->registerService( new $class , $options );
                }
            }

            if( $configLoader->isLoaded('database') ) {
                $kernel->registerService( new \Phifty\Service\DatabaseService );
            }
        }
    }

    /**
     * @param string $env Environment type
     */
    static function createKernel($env = null)
    {
        return new Kernel( $env );
    }
}


}
namespace {
    use Phifty\Bootstrap;

    global $kernel;

    /**
    * kernel() is a global shorter helper function to get Phifty\Kernel instance.
    *
    * Initialize kernel instance, classloader, plugins and services.
    */
    function kernel() 
    {
        global $kernel;
        if( $kernel )
            return $kernel;

        Bootstrap::initConstants();
        $classloader = Bootstrap::initClassLoader();
        $kernel      = Bootstrap::createKernel();
        Bootstrap::bootKernel($kernel,$classloader);
        $kernel->init();
        return $kernel;
    }
}
