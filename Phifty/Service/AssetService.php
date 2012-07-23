<?php
namespace Phifty\Service;
use AssetKit;
use Exception;

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
        $assetFile = PH_APP_ROOT . DIRECTORY_SEPARATOR . '.assetkit';

        if( ! file_exists($assetFile) ) {
            throw new Exception("$assetFile not found.");
            return;
        }

        $config = new AssetKit\Config( $assetFile , 
            $kernel->environment === 'production' 
                ? array( 'cache' => true ) 
                : array() 
        );

        $kernel->asset = function() use ($kernel,$config) {
            $loader = new AssetKit\AssetLoader($config);
            $writer = new AssetKit\AssetWriter($config);
            $writer->env($kernel->environment);

            if( $kernel->namespace ) {
                $writer->name( $kernel->namespace );
            }

            $writer->in('/assets');

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

