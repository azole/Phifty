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

        $kernel->plugin = function() {
            return PluginManager::getInstance();
        };
    }

}






