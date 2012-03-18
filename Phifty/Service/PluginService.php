<?php
namespace Phifty\Service;
use Phifty\PluginManager;

class PluginService
    implements ServiceInterface
{

    public function register($kernel, $options = array() )
    {
        $config = $kernel->config->get('framework','plugins');
        if( $config->isEmpty() )
            return;

        $manager = PluginManager::getInstance();
        foreach( $config as $pluginName => $options ) {
            //$this->plugin->loadFromList( $pluginConfigs );
        }

        $kernel->plugin = function() use ($manager) {
            return $manager;
        };
    }

}






