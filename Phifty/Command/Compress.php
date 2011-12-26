<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\Web;
use Phifty\WebPath;
use Phifty\WebUtils;

class Compress extends \Phifty\Command
{

    
    function compressApp($app)
    {
        $web = new Web;
        $id = strtolower( get_class($app) );
        {
            # $minifiedFile = PH_APP_ROOT . "/webroot/static/minified/$id.min.css";
            $webDir = $app->locate() . DIRECTORY_SEPARATOR . 'web';
            $minifiedFile =  $webDir . "/css/minified.css";
            if( ! file_exists( "$webDir/css" ) )
                mkdir( "$webDir/css" , 0755 , true );

            if( count($app->css()) ) {
                $this->log( "Compressing $id css => $minifiedFile ..." );
                $files = $web->globPaths( $webDir , $app->css() );
                WebUtils::minifyCssFiles( $files , $minifiedFile );
            }
        }

        {
            $webDir = $app->locate() . DIRECTORY_SEPARATOR . 'web';
            // $minifiedFile = PH_APP_ROOT . "/webroot/static/minified/$id.min.js";
            $minifiedFile =  $webDir . "/js/minified.js";
            if( ! file_exists( "$webDir/js" ) )
                mkdir( $webDir . '/js' , 0755 , true );
            if( count($app->js()) ) {
                $this->log( "Compressing $id js => $minifiedFile ..." );
                $files = $web->globPaths( $webDir , $app->js() );
                WebUtils::minifyJsFiles( $files , $minifiedFile );
            }
        }
    }

    function run()
    {
        $core = \Core\Application::getInstance();
        $this->compressApp( $core );

        $plugins = webapp()->plugin->getPlugins();
        if( $plugins ) {
            $this->log( "Compressing plugins ..." );
            foreach( $plugins as $plugin ) {
                $this->compressApp( $plugin );
            }
        }
        $this->log( "Done" );
    }
}
