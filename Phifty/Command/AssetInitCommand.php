<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Exception;
use AssetToolkit\Asset;
use AssetToolkit\AssetConfig;

class AssetInitCommand extends AssetBaseCommand
{

    public function execute() 
    {
        $loader = $this->getAssetLoader();
        $kernel = kernel();

        $this->logger->info("Finding assets from applications...");
        foreach( $kernel->applications as $application ) {
            $this->registerBundleAssets($application);
        }

        $this->logger->info("Finding assets from plugins...");
        foreach( $kernel->plugins as $plugin ) {
            $this->registerBundleAssets($plugin);
        }
        $this->getAssetConfig()->save();
    }
}

