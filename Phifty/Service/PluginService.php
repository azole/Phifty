<?php
namespace Phifty\Service;
use Phifty\PluginManager;

class PluginService
    implements ServiceInterface
{

    public function getId() { return 'Plugin'; }

    public function register($kernel, $options = array() )
    {
        $config = $kernel->config->get('framework','Plugins');
        if( $config === null || $config->isEmpty() )
            return;

        // depends on classloader
        $manager = PluginManager::getInstance();
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






