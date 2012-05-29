<?php
namespace Phifty\Command;
use CLIFramework\Command;
use AssetKit\Config;
use AssetKit\Installer;
use AssetKit\LinkInstaller;

/**
 * When running asset:init command, we should simply register app/plugin assets 
 * into .assetkit file.
 *
 * Then, By running asset:update command, phifty will install assets into webroot.
 */
class AssetUpdateCommand extends Command
{
    function options($opts)
    {
        $opts->add('l|link','use symbolic link');
    }

    function execute() 
    {
        $options = $this->options;
        $config = new Config('.assetkit');
        $installer = $options->link
                ? new LinkInstaller
                : new Installer;

        foreach( $config->getAssets() as $name => $asset ) {
            $this->logger->info("Updating $name ...");
            $asset->initResource(true); // update it

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );

            $export = $asset->export();
            $config->addAsset( $asset->name , $export );
            $config->save();
        }
        $this->logger->info("Done");
    }
}

