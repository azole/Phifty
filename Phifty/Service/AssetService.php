<?php
namespace Phifty\Service;
use AssetKit;

class AssetService
    implements ServiceInterface
{

    function getId()
    {
        return 'asset';
    }

    /**
     *
     * $kernel->asset->loader
     * $kernel->asset->writer
     */
    function register($kernel, $options = array() ) 
    {
        $config = new AssetKit\Config( PH_APP_ROOT . '/.assetkit' , 
            $kernel->environment === 'production' 
                ? array( 'cache' => true ) 
                : array() 
        );

        $kernel->asset = function() use ($kernel,$config) {
            $loader = new AssetKit\AssetLoader($config);
            $writer = new AssetKit\AssetWriter($config);
            $writer->env($kernel->environment);

            if( $kernel->namespace )
                $writer->name( $kernel->namespace );

            // cache
            if( isset($kernel->cache) ) {
                $writer->cache( $kernel->cache );
            }
            return (object) array( 
                'loader' => $loader,
                'writer' => $writer,
            );
        };
    }

}


