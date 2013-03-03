<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Exception;
use AssetToolkit\Asset;
use AssetToolkit\AssetConfig;

class AssetInitCommand extends Command
{
    public function registerAsset($config,$dir)
    {
        $manifestPath = substr(
            $dir  . DIRECTORY_SEPARATOR . 'manifest.yml', 
            strlen(PH_APP_ROOT) + 1 );

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $asset = new Asset($manifestPath);
        $asset->config = $config;
        $asset->initResource(true); // update it

        // export config to assetkit file
        $config->addAsset( $asset->name , $asset->export() );

        $this->logger->info("{$asset->name} added.", 1);
        $config->save();
    }

    public function execute() 
    {
        $config = new AssetConfig('.assetkit.php');
        $kernel = kernel();

        $this->logger->info("Finding assets from applications...");
        foreach( $kernel->applications as $application ) {
            $this->logger->info( ' - ' . get_class($application) );
            foreach( $application->getAssetDirs() as $dir ) {
                $this->registerAsset($config,$dir);
            }
        }

        $this->logger->info("Finding assets from plugins...");
        foreach( $kernel->plugins as $plugin ) {
            $this->logger->info( ' - ' . get_class($plugin) );
            foreach( $plugin->getAssetDirs() as $dir ) {
                $this->registerAsset($config,$dir);
            }
        }
    }
}

