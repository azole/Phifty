<?php
namespace Phifty\Service;
use UniversalCache\ApcCache;
use UniversalCache\FileSystemCache;
use UniversalCache\MemcacheCache;
use UniversalCache\UniversalCache;

class CacheService
    implements ServiceInterface
{
    public function getId() { return 'cache'; }

    public function register($kernel, $options = array() )
    {
        $kernel->cache = function() use ($kernel) {
            // return new ApcCache( $self->appName );

            /*
            if ( extension_loaded('apc') )
                $b[] = $kernel->apc;
            */

            $cache = new UniversalCache(array());
            if ( extension_loaded('apc') ) {
                $cache->addBackend(new ApcCache( array( 'namespace' => $kernel->config->get('framework','ApplicationID') ) ));
            }
            if ( extension_loaded('memcache') ) {
                $cache->addBackend(new MemcacheCache( array( 
                    'servers' => array( array('localhost',11211) )
                )));
            }
            return $cache;
        };
    }
}
