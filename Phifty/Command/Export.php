<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\PluginPool;
use Phifty\WidgetFinder;

/*
 * Export plugin web dirs to app webroot.
 */
class Export extends \Phifty\Command
{
	var $longOpts = array('c|clean');

    function run()
    {
		$options = $this->getOptions();


        $app          = \AppKernel::one();
        $webroot      = $app->getWebRootDir();
        $webPluginDir = $app->getWebPluginDir();
        $webWidgetDir = $app->getWebWidgetDir();
        $appWebDir    = $app->getAppWebDir();
        $coreWebDir   = $app->getCoreWebDir();

		if( $options->clean ) {
			$this->log( "Removing webroot/ph");
			$unlinks = array();
			$unlinks[] = FileUtils::path_join( $webroot , 'ph' , $app->getAppName() );
			$unlinks[] = FileUtils::path_join( $webroot , 'ph' , 'Core' );
			foreach( $unlinks as $unlink ) {
				$this->log("Unlinking $unlink ...");
				if( file_exists( $unlink ) )
					unlink( $unlink );
			}
			return;
		}

		$this->log( "Exporting web directory to webroot..." );

        /* Make directories */
		$dirs = array();
		$dirs[] = $webroot;
		$dirs[] = $webPluginDir;
        $dirs[] = $webWidgetDir;
        $dirs[] = $appWebDir;
        $dirs[] = $coreWebDir;
        $dirs[] = $webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';
		foreach( $dirs as $dir )
			FileUtils::mkpath( $dir , true );

        system( 'chmod -vR 777 ' . $webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );

		$links = array();
		$links[] = array( $appWebDir , FileUtils::path_join( $webroot , 'ph' , $app->getAppName() ) );
		$links[] = array( $coreWebDir , FileUtils::path_join( $webroot , 'ph' , 'Core' ) );

		foreach( $links as $link ) {
            if( file_exists( $link[1] ) ) {
                $this->log("\tremove link {$link[1]}");
                unlink( $link[1] );
            }
            $this->log("\tcreate link {$link[1]}");
            symlink( $link[0] , $link[1] );
		}

		/* 
		 * get all plugins 
		 *
		 * and link the plugin web directory to web/
		 *
		 * */
        $pool = \Phifty\PluginPool::one();
        foreach( $pool->getPlugins() as $plugin ) 
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
            $this->log( "\tLinking: $pluginWebDir to $target" );
            if( ! file_exists( $target ) )
                symlink( $pluginWebDir , $target );
        }


        /*
         * Export widget web dirs 
         *
         * Export widgets/Galleria/web to PH_APP_ROOT/webroot/ph/widgets/Galleria
         */
        $widgets = WidgetFinder::findAll();
        // $webWidgetDir;
        $this->log("Creating links for widgets");
        foreach( $widgets as $name => $widgetInfo ) {
            $source = $widgetInfo->web;
            $target = FileUtils::path_join( $webWidgetDir , $widgetInfo->name );

            if( file_exists( $target ) )
                unlink( $target );

            if( file_exists($source) ) {
                $this->log("\tcreate link $name\t\t$target");
                symlink( $source, $target );
            }
            else {
                throw new Exception( "$source does not exist. can not create $target." );
            }
        }

		$this->log( "Done" );
    }
}

?>
