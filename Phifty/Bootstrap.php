<?php
namespace Phifty {
use Phifty\Kernel;
use Universal\ClassLoader\SplClassLoader;

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

    static function initClassLoader() {
        // create spl classloader
        $spl = new SplClassLoader;
        $spl->addNamespace(array( 
            'Phifty'     => PH_ROOT . '/src',
            'ActionKit'  => PH_ROOT . '/src',
            'I18NKit'    => PH_ROOT . '/src',
            'Universal'  => PH_ROOT . '/vendor/universal/src',
            'AssetKit'   => PH_ROOT . '/vendor/assetkit/src',
            'LazyRecord' => PH_ROOT . '/vendor/lazyrecord/src',
            'FormKit'    => PH_ROOT . '/vendor/formkit/src',
            'Roller'     => PH_ROOT . '/vendor/roller/src',
        ));
        $spl->addFallback( PH_ROOT . '/vendor/pear' );
        $spl->useIncludePath(true);
        $spl->register();
        return $spl;
    }

    static function bootKernel($kernel,$spl) {

        $classloaderService = new \Phifty\Service\ClassLoaderService;
        $classloaderService->setClassLoader($spl);
        $kernel->registerService( $classloaderService );

        // We load other services from the definitions in config file
        // Simple load three config files (framework.yml, database.yml, application.yml)
        $configService = new \Phifty\Service\ConfigService;

        if( $frameworkLoaded = file_exists( PH_APP_ROOT . '/config/framework.php') )
            $configService->load('framework', PH_APP_ROOT . '/config/framework.php');

        // This is for DatabaseService
        if( $dbLoaded = file_exists( PH_APP_ROOT . '/config/database.php') )
            $configService->load('database', PH_APP_ROOT . '/config/database.php');

        // Only load testing configuration when environment 
        // is 'testing'
        if( getenv('PHIFTY_ENV') === 'testing' ) {
            if( file_exists( PH_APP_ROOT . '/config/testing.php' ) ) {
                $configService->load('testing', PH_APP_ROOT . '/config/testing.php' );
            }
        }

        // Config for application, services does not depends on this config file.
        if( $appLoaded = file_exists( PH_APP_ROOT . '/config/application.php') )
            $configService->load('application', PH_APP_ROOT . '/config/application.php' );

        // load config service first.
        $kernel->registerService( $configService );

        // load a event service, so that we can bind events in Phifty
        // currently we are working on a CTEvent extension, which provides a better 
        // performance than pure PHP class.
        $kernel->registerService( new \Phifty\Service\EventService );

        // if the framework config is defined.
        if( $frameworkLoaded ) {
            if( $services = $kernel->config->get('framework','Services') ) {
                foreach( $services as $name => $options ) {
                    // not full qualified classname
                    $class = ( false === strpos($name,'\\') ) ? ('Phifty\\Service\\' . $name) : $name;
                    $kernel->registerService( new $class , $options );
                }
            }

            if( $dbLoaded ) {
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
