<?php
namespace Phifty;
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

    /**
     *
     * @param string $env Environment type
     */
    static function bootstrap( $env = null )
    {
        // create spl classloader
        $spl = new SplClassLoader;
        $spl->addNamespace(array( 
            'Phifty'     => PH_ROOT . '/src',
            'ActionKit'  => PH_ROOT . '/src',
            'I18NKit'    => PH_ROOT . '/src',
            'AssetKit'   => PH_ROOT . '/vendor/assetkit/src',
            'LazyRecord' => PH_ROOT . '/vendor/lazyrecord/src',
            'FormKit'    => PH_ROOT . '/vendor/formkit/src',
            'Roller'     => PH_ROOT . '/vendor/roller/src',
        ));
        $spl->addFallback( PH_ROOT . '/vendor/pear' );
        $spl->useIncludePath(true);
        $spl->register();

        global $kernel;

        $kernel = new Kernel( $env );
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
        return $kernel;
    }
}





