<?php
namespace Phifty\Service;
use Phifty\PluginManager;

class PluginService
    implements ServiceInterface
{

    public function register($kernel, $options = array() )
    {
        $kernel->plugin = function() {
            return PluginManager::getInstance();
        };
    }

}






