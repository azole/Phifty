<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\FileUtils;
use Phifty\CodeTemplate;

class InitCommand extends Command
{

    function execute()
    {
        $kernel = kernel();
		$this->logger->info( "Initializing phifty dirs..." );
		$this->logger->info( "Webroot: " . $kernel->webroot );

        $dirs = array();
        $dirs[] = FileUtils::path_join( $kernel->rootDir , 'cache' , 'view' );
        $dirs[] = FileUtils::path_join( $kernel->rootDir , 'cache' , 'config' );
		$dirs[] = $kernel->webroot;

        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'ph' . DIRECTORY_SEPARATOR . 'plugins';

		/* hard links */
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'css';
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'js';
        $dirs[] = $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';

        FileUtils::mkpath($dirs,true);

        $codegen = new CodeTemplate;

		$htaccessFiln = $kernel->webroot . DIRECTORY_SEPARATOR . '.htaccess';
        $codegen->renderFile( $htaccessFile, 'htaccess' );

        $webrootIndex = $kernel->webroot . DIRECTORY_SEPARATOR . 'index.php';
        $codegen->renderFile( $webrootIndex , 'webroot_index.php' );

		$this->logger->info( "Changing permissions..." );
        $chmods = array();
        $chmods[] = array( "ga+rw" , "cache" );

        foreach( $kernel->applications as $n => $app ) {
            $chmods[] = array( "ga+rw" , $kernel->app($n)->getWebDir() );
        }

        $chmods[] = array( "ga+rw" , $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );
        foreach( $chmods as $mod ) {
			$this->logger->info( "{$mod[0]} {$mod[1]}", 1 );
            system("chmod -R {$mod[0]} {$mod[1]}");
        }

    }
}

