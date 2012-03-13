<?php
namespace Phifty\Service;
use Lazy\ConfigLoader;
use Lazy\ConnectionManager;

class DatabaseService
    implements ServiceInterface
{
    function register($kernel)
    {
        $loader = \Lazy\ConfigLoader::getInstance();
        if( ! $loader->loaded ) { 
            $loader->load( PH_APP_ROOT . '/.lazy.php');
            $loader->init();  // init datasource and connection
        }

        $kernel->db = function() {
            $conm = ConnectionManager::getInstance();
            return $conm->getConnection();
        };
    }
}


