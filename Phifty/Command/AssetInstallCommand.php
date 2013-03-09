<?php
namespace Phifty\Command;
use CLIFramework\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;

/**
 * When running asset:init command, we should simply register app/plugin assets 
 * into .assetkit file.
 *
 * Then, By running asset install command, phifty will install assets into webroot.
 */
class AssetInstallCommand extends AssetBaseCommand
{
    function options($opts)
    {
        $opts->add('l|link','use symbolic link');
    }

    function execute() 
    {
        $options = $this->options;
        $config = $this->getAssetConfig();

        $installer = $options->link
                ? new LinkInstaller
                : new Installer;

        $installer->logger = $this->logger;
        $loader = $this->getAssetLoader();
        $kernel = kernel();

        $this->logger->info("Installing assets from applications...");
        foreach( $kernel->applications as $application ) {
            $assetNames = $application->assets();
            $assets = $loader->loadAssets($assetNames);
            foreach( $assets as $asset ) {
                $this->logger->info("Installing {$asset->name} ...");
                $installer->install( $asset );
            }
        }

        $this->logger->info("Installing assets from plugins...");
        foreach( $kernel->plugins as $plugin ) {
            $assetNames = $plugin->assets();
            $assets = $loader->loadAssets($assetNames);
            foreach( $assets as $asset ) {
                $this->logger->info("Installing {$asset->name} ...");
                $installer->install( $asset );
            }
        }
        $this->logger->info("Done");
    }
}

