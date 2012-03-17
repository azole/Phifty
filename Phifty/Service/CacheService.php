<?php
namespace Phifty\Service;
use CacheKit\CacheKit;
use CacheKit\ApcCache;

class CacheService
    implements ServiceInterface
{
    public function register($kernel, $options = array() )
    {
        $kernel->cache = function() use ($kernel) {
            $b = array();

            // return new ApcCache( $self->appName );

            /*
            if( extension_loaded('apc') )
                $b[] = $kernel->apc;
            */

            /*
            if( extension_loaded('memcache') )
                $b[] = new \CacheKit\MemcacheCache( array( array('localhost',11211) ) );
            */
            return new CacheKit($b);
        };
    }
}





