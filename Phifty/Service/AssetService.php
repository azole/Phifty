<?php
namespace Phifty\Service;
use AssetToolkit;
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
        $assetFile = PH_APP_ROOT . DIRECTORY_SEPARATOR . '.assetkit.php';

        if( ! file_exists($assetFile) ) {
            throw new Exception("$assetFile not found.");
            return;
        }

        $config = new AssetToolkit\AssetConfig( $assetFile , 
            $kernel->environment === 'production' 
                ? array( 'cache' => true ) 
                : array() 
        );

        $kernel->asset = function() use ($kernel,$config) {
            $loader = new AssetToolkit\AssetLoader($config);
            $compiler = new AssetToolkit\AssetCompiler($config,$loader);
            $render = new AssetToolkit\AssetRender($config,$loader);

            if( $kernel->namespace ) {
                $compiler->setNamespace( $kernel->namespace );
            }

            return (object) array( 
                'loader' => $loader,
                'render' => $render,
                'compiler' => $compiler,
            );
        };
    }
}

