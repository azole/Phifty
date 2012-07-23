<?php
namespace Phifty\Service;
use LazyRecord\ConfigLoader;
use LazyRecord\ConnectionManager;

class DatabaseService
    implements ServiceInterface
{

    public function getId() { return 'database'; }

    public function register($kernel, $options = array() )
    {
        $config = $kernel->config->stashes['database'];
        if( empty($config) )
            return;

        $loader = ConfigLoader::getInstance();
        if( ! $loader->loaded ) { 
            $loader->load( $config );
            $loader->init();  // init data source and connection
        }
        $kernel->db = function() {
            $conm = ConnectionManager::getInstance();
            return $conm->getConnection();
        };
    }

}


