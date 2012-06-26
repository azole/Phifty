<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\FileUtils;

class InitCommand extends Command
{

    function options($opts)
    {
        $init = new AssetInitCommand;
        $install = new AssetInstallCommand;
        $install->options($opts);
    }

    function execute()
    {
        $kernel = kernel();
        $this->logger->info( "Initializing phifty dirs..." );
        $this->logger->info( "Webroot: " . $kernel->webroot );

        $dirs = array();
        $dirs[] = FileUtils::path_join( $kernel->rootDir , 'cache' , 'view' );
        $dirs[] = FileUtils::path_join( $kernel->rootDir , 'cache' , 'config' );
        $dirs[] = 'locale';
        $dirs[] = $kernel->webroot;

        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'ph' . DIRECTORY_SEPARATOR . 'plugins';

        /* for hard links */
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'css';
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'js';
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';

        FileUtils::mkpath($dirs,true);

// TODO: create .htaccess file

        $this->logger->info( "Changing permissions..." );
        $chmods = array();
        $chmods[] = array( "og+rw" , "cache" );

        foreach( $kernel->applications as $n => $app ) {
            $chmods[] = array( "og+rw" , $kernel->app($n)->getWebDir() );
        }

        $chmods[] = array( "og+rw" , $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );
        foreach( $chmods as $mod ) {
            $this->logger->info( "{$mod[0]} {$mod[1]}", 1 );
            system("chmod -R {$mod[0]} {$mod[1]}");
        }


        $this->logger->info("Installing Assets");


        // Add command factory to CLIFramework Command class.
        $init = new AssetInitCommand;
        $install = new AssetInstallCommand;
        $init->application = $this->application;
        $init->options = $this->options;
        $init->executeWrapper(array());

        $install->application = $this->application;
        $install->options = $this->options;
        $install->executeWrapper(array());
    }
}

