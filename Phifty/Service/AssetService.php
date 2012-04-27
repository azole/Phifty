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


    function register($kernel, $options = array() ) 
    {
        $config = new AssetKit\Config( PH_APP_ROOT . '/.assetkit' );
        $kernel->assetLoader = function($kernel) use ($config) {
            return new AssetKit\AssetLoader( $config );
        };
        $kernel->assetWriter = function($kernel) use ($config) {
            $writer = new AssetKit\AssetWriter($config);
            $writer->env($kernel->environment);
            if( $kernel->namespace ) {
                $writer->name($kernel->namespace);
            }
            return $writer;
        };
    }

}


