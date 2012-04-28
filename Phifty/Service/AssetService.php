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
        $config = new AssetKit\Config( PH_APP_ROOT . '/.assetkit' );
        $kernel->asset = function() use ($kernel,$config) {
            $loader = new AssetKit\AssetLoader($config);
            $writer = new AssetKit\AssetWriter($config);
            $writer->env($kernel->environment);

            // cache
            if( isset($kernel->cache) ) {
                $writer->cache( $kernel->cache );
            }

            if( $kernel->namespace ) {
                $writer->name($kernel->namespace);
            }

            return (object) array( 
                'loader' => $loader,
                'writer' => $writer,
            );
        };
    }

}


