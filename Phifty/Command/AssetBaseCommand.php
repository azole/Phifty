<?php
namespace Phifty\Command;
use CLIFramework\Command;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetConfig;
use AssetToolkit\ResourceUpdater;


class AssetBaseCommand extends Command
{

    public function getAssetConfig()
    {
        static $config;
        if($config)
            return $config;
        return $config = new AssetConfig('.assetkit.php');
    }

    public function getAssetLoader()
    {
        return new AssetLoader($this->getAssetConfig());
    }

    public function registerBundleAssets($bundle)
    {
        $config = $this->getAssetConfig();
        $this->logger->info( ' - ' . get_class($bundle) );
        $cwd = getcwd();
        foreach( $bundle->getAssetDirs() as $dir ) {
            if( file_exists($dir) ) {
                $dir = substr($dir, strlen($cwd) + 1 );
                $asset = $config->registerAssetFromPath($dir);
                $this->updateAssetResource($asset);
            }
        }
    }

    public function updateAssetResource($asset)
    {
        $updater = new ResourceUpdater;
        $updater->update($asset);
    }
}



