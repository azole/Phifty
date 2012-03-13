<?php
namespace Phifty\Service;
use Phifty\PluginManager;

class PluginService
    implements ServiceInterface
{

    public function register($kernel)
    {
        $kernel->plugin = function() {
            return PluginManager::getInstance();
        };
    }

}






