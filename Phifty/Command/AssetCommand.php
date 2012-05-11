<?php
namespace Phifty\Command;
use CLIFramework\Command;

class AssetCommand extends Command
{
    function execute() 
    {
        $config = new \AssetKit\Config('.assetkit');
        $kernel = kernel();


        $this->logger->info("Finding assets from applications...");
        foreach( $kernel->applications as $application ) {
            foreach( $plugin->getAssetDirs() as $dir ) {
                $manifestPath = $dir  . DIRECTORY_SEPARATOR . 'manifest.yml';
                if( ! file_exists($manifestPath)) 
                    throw new Exception( "$manifestPath does not exist." );

                $asset = new \AssetKit\Asset($manifestPath);
                $asset->config = $config;
                $asset->initResource(true); // update it

                $installer = new \AssetKit\Installer;
                $installer->install( $asset );

                $export = $asset->export();
                $config->addAsset( $asset->name , $export );

                $this->logger->info("Saving config...");
                $config->save();
            }
        }

        $this->logger->info("Finding assets from plugins...");
        foreach( $kernel->plugins as $plugin ) {
            foreach( $plugin->getAssetDirs() as $dir ) {
                $manifestPath = $dir  . DIRECTORY_SEPARATOR . 'manifest.yml';
                if( ! file_exists($manifestPath)) 
                    throw new Exception( "$manifestPath does not exist." );

                $asset = new \AssetKit\Asset($manifestPath);
                $asset->config = $config;
                $asset->initResource(true); // update it

                $installer = new \AssetKit\Installer;
                $installer->install( $asset );

                $export = $asset->export();
                $config->addAsset( $asset->name , $export );

                $this->logger->info("Saving config...");
                $config->save();
            }
        }
    }
}

