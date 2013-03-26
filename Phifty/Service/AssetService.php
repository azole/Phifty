<?php
namespace Phifty\Service;
use AssetToolkit;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetCompiler;
use AssetToolkit\AssetRender;
use AssetToolkit\Cache;
use UniversalCache\ApcCache;
use Exception;

class AssetService
    implements ServiceInterface
{

    public function getId()
    {
        return 'asset';
    }

    /**
     *
     * $kernel->asset->loader
     * $kernel->asset->writer
     */
    public function register($kernel, $options = array() )
    {
        $kernel->asset = function() use ($kernel) {
            $assetFile = PH_APP_ROOT . DIRECTORY_SEPARATOR . '.assetkit.php';
            if ( ! file_exists($assetFile) ) {
                throw new Exception("$assetFile not found.");
            }

            $config = new AssetConfig( $assetFile ,
                $kernel->environment === 'production'
                ? array( 'environment' => AssetConfig::PRODUCTION )
                : array( 'environment' => AssetConfig::DEVELOPMENT )
            );

            $cache = Cache::create($config);
            $config->setCache($cache);

            $loader   = new AssetLoader($config);
            $render   = new AssetRender($config,$loader);
            $compiler = $render->getCompiler();
            $compiler->defaultJsCompressor = 'uglifyjs';
            // $compiler->enableProductionFstatCheck();

            return (object) array(
                'loader' => $loader,
                'config' => $config,
                'render' => $render,
                'compiler' => $compiler,
            );
        };
    }
}
