<?php
namespace Phifty\Service;
use Phifty\Plugin\PluginManager;

class PluginService
    implements ServiceInterface
{

    public function getId() { return 'Plugin'; }

    public function register($kernel, $options = array() )
    {
        $config = $kernel->config->get('framework','Plugins');
        if( $config === null || $config->isEmpty() ) {
            return;
        }


        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $manager = PluginManager::getInstance();
        if( PH_APP_ROOT !== PH_ROOT )
            $manager->registerPluginDir( PH_APP_ROOT . DIRECTORY_SEPARATOR . 'plugins' );
        $manager->registerPluginDir( PH_ROOT . DIRECTORY_SEPARATOR . 'plugins' );

        foreach( $config as $pluginName => $config ) {
            $kernel->classloader->addNamespace(array( 
                $pluginName => array( 
                    $kernel->rootPluginDir,
                    $kernel->frameworkPluginDir,
                )
            ));
            $manager->load( $pluginName , $config );
        }
        $kernel->plugins = function() use ($manager) {
            return $manager;
        };
    }

}


