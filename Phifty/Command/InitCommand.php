<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\FileUtils;

function copy_if_not_exists($source,$dest) {
    if( ! file_exists($dest) ) {
        copy($source,$dest);
    }
}

class InitCommand extends Command
{
    function brief() {
        return 'Initialize phifty project files, directories and permissions.';
    }

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
        $dirs[] = FileUtils::path_join( PH_APP_ROOT , 'cache' , 'view' );
        $dirs[] = FileUtils::path_join( PH_APP_ROOT , 'cache' , 'config' );
        $dirs[] = 'locale';
        $dirs[] = 'applications';
        $dirs[] = 'bin';
        $dirs[] = 'plugins';
        $dirs[] = 'config';
        $dirs[] = 'webroot';

        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'ph' . DIRECTORY_SEPARATOR . 'plugins';

        /* for hard links */
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'css';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'js';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';
        FileUtils::mkpath($dirs,true);

// TODO: create .htaccess file

        $this->logger->info( "Changing permissions..." );
        $chmods = array();
        $chmods[] = array( "og+rw" , "cache" );

        foreach( $kernel->applications as $n => $app ) {
            $webDir = $kernel->app($n)->getWebDir();
            if( file_exists($webDir) )
                $chmods[] = array( "og+rw" , $webDir );
        }

        $chmods[] = array( "og+rw" , $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );
        foreach( $chmods as $mod ) {
            $this->logger->info( "{$mod[0]} {$mod[1]}", 1 );
            system("chmod -R {$mod[0]} {$mod[1]}");
        }

        $this->logger->info("Linking bin/phifty");
        if( ! file_exists('bin/phifty') ) {
            symlink(  '../phifty/bin/phifty', 'bin/phifty' );
        }

        # init config
        $this->logger->info("Copying config files");
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'config','framework.app.yml'), FileUtils::path_join(PH_APP_ROOT,'config','framework.yml') );
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'config','application.dev.yml'), FileUtils::path_join(PH_APP_ROOT,'config','application.yml') );
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'db','config','database.app.yml'), FileUtils::path_join(PH_APP_ROOT,'db','config','database.yml') );

        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'webroot','index.php'), FileUtils::path_join(PH_APP_ROOT,'webroot','index.php') );
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'webroot','.htaccess'), FileUtils::path_join(PH_APP_ROOT,'webroot','.htaccess') );

        if( PH_ROOT !== PH_APP_ROOT ) {
            // link 'assets/' to 'phifty/assets/'
            if( ! file_exists('assets') )
                symlink( 'phifty' . DIRECTORY_SEPARATOR . 'assets' , 'assets' );

            // link 'vendor/' to 'phifty/vendor/'
            if( ! file_exists('vendor') )
                symlink( 'phifty' . DIRECTORY_SEPARATOR . 'vendor' , 'vendor' );
        }

        $this->logger->info('Application is initialized, please edit your config files and run:');

        echo <<<DOC

    $ bin/phifty build-conf
    $ bin/phifty asset

    $ lazy build-conf config/database.yml

DOC;
    }
}

