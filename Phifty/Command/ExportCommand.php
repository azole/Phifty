<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\Asset\AssetFinder;
use CLIFramework\Command;


/*
 * Export plugin web dirs to app webroot.
 */
class ExportCommand extends Command
{

    public function usage()
    {
        return 'export';
    }

    public function brief()
    {
        return 'export application web paths to http webroot.';
    }

    public function execute()
    {
		$options = $this->getOptions();


        $kernel       = kernel();
        $webroot      = $kernel->webroot;
        $webPluginDir = $kernel->getWebPluginDir();
        $webAssetDir  = $kernel->getWebAssetDir();

		if( $options->clean ) {
			$this->logger->info( "Removing webroot/ph");
			$unlinks = array();
            foreach( $kernel->applications as $appname => $app ) {
                $path = FileUtils::path_join( $webroot , 'ph' , $appname );
				$this->logger->info("Unlinking $path ...");
				if( file_exists( $path ) )
					unlink( $path );
            }
			return;
		}

		$this->logger->info( "Exporting web directory to webroot..." );


        /* Make directories */
		$dirs = array();
		$dirs[] = $webroot;
		$dirs[] = $webPluginDir;
        $dirs[] = $webAssetDir;

        foreach( $kernel->applications as $appname => $app ) {
            $dirs[] = $app->getWebDir();
        }


        $dirs[] = $webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';
		foreach( $dirs as $dir )
			FileUtils::mkpath( $dir , true );

        system( 'chmod -vR 777 ' . $webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );

		$links = array();
        foreach( $kernel->applications as $appname => $app ) {
            $links[] = array( $app->getWebDir() , FileUtils::path_join( $webroot , 'ph' , $appname ) );
        }

		foreach( $links as $link ) {
            list($from,$target) = $link;
            if( file_exists( $target ) ) {
                $this->logger->info("remove link $target");
                unlink( $target );
            }
            $this->logger->info("create link $target",1);
            symlink( $from , $target );
		}

		/* 
		 * get all plugins 
		 *
		 * and link the plugin web directory to web/
		 *
		 * */
        foreach( kernel()->plugins as $plugin ) 
        {
            // create links
            // var_dump( $plugin->getName() ); 
            $name = $plugin->getName();
            $target = FileUtils::path_join( $webPluginDir , $name );

            // find source plugin path
            $pluginDir = \Phifty\Plugin::locatePlugin( $name );
            $pluginWebDir =  FileUtils::path_join( $pluginDir , 'web' );
            if( ! file_exists( $pluginWebDir ) ) 
                continue;

			/*
			 * plugins/User/web => webroot/plugin/User
			 * plugins/{plugin}/web => webroot/plugin/User
			 */
            $this->logger->info( "create link $target", 1 );
            if( ! file_exists( $target ) )
                symlink( $pluginWebDir , $target );
        }


        /*
         * Export asset web dirs 
         *
         * Export assets/Galleria/web to PH_APP_ROOT/webroot/ph/assets/Galleria
         */
        $assets = AssetFinder::findAll();
        // $webAssetDir;
        $this->logger->info("Creating links for assets");
        foreach( $assets as $name => $assetInfo ) {
            $source = $assetInfo->web;
            $target = FileUtils::path_join( $webAssetDir , $assetInfo->name );

            if( file_exists( $target ) )
                unlink( $target );

            if( file_exists($source) ) {
                $this->logger->info("\tcreate link $name\t\t$target");
                symlink( $source, $target );
            }
            else {
                throw new Exception( "$source does not exist. can not create $target." );
            }
        }

		$this->logger->info( "Done" );
    }
}

