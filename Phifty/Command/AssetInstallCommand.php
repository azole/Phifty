<?php
namespace Phifty\Command;
use CLIFramework\Command;
use AssetKit\AssetConfig;
use AssetKit\Installer;
use AssetKit\LinkInstaller;

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
        $assets = $loader->loadAll();

        foreach( $assets as $asset ) {
            $this->logger->info("Installing {$asset->name} ...");
            $installer->install( $asset );
        }
        $this->logger->info("Done");
    }
}

