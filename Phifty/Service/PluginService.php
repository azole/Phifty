<?php
namespace Phifty\Service;
use Phifty\Plugin\PluginManager;

class PluginService
    implements ServiceInterface
{

    public function getId() { return 'Plugin'; }


    /**
     *
     * @param Phifty\Kernel $kernel Kernel object.
     * @param array $options Plugin service options.
     */
    public function register($kernel, $options = array() )
    {
        // here we check plugins stash to decide what to load.
        $config = $kernel->config->get('framework','Plugins');
        if( $config === null || $config->isEmpty() ) {
            return;
        }


        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $manager = PluginManager::getInstance();
        $kernel->plugins = function() use ($manager) {
            return $manager;
        };

        // default plugin paths
        if( PH_APP_ROOT !== PH_ROOT )
            $manager->registerPluginDir( PH_APP_ROOT . DIRECTORY_SEPARATOR . 'plugins' );
        $manager->registerPluginDir( PH_ROOT . DIRECTORY_SEPARATOR . 'plugins' );

        if ( isset($options["Dirs"]) ) {
            foreach( $options["Dirs"] as $dir ) {
                $manager->registerPluginDir($dir);
            }
        }

        foreach( $config as $pluginName => $config ) {
            $kernel->classloader->addNamespace(array( 
                $pluginName => array( 
                    $kernel->rootPluginDir,
                    $kernel->frameworkPluginDir,
                )
            ));
            $manager->load( $pluginName , $config );
        }

        // initialize all loaded plugin
        $manager->init();
    }

}


