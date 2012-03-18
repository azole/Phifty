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

		$this->logger->info( "Webroot: " . $kernel->getRootDir() );

        $dirs = array();
        $dirs[] = FileUtils::path_join( $kernel->getRootDir() , 'cache' , 'view' );
        $dirs[] = FileUtils::path_join( $kernel->getRootDir() , 'cache' , 'config' );
		$dirs[] = $kernel->getWebRootDir();

        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'ph' . DIRECTORY_SEPARATOR . 'plugins';

		/* hard links */
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'css';
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'js';
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';

        FileUtils::mkpath($dirs,true);

        $codegen = new CodeTemplate;

		$htaccessFile = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . '.htaccess';
        $codegen->renderFile( $htaccessFile, 'htaccess' );

        $webrootIndex = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'index.php';
        $codegen->renderFile( $webrootIndex , 'webroot_index.php' );

		$this->logger->info( "Changing permissions..." );
        $chmods = array();
        $chmods[] = array( "ga+rw" , "cache" );
        $chmods[] = array( "ga+rw" , $kernel->getCoreWebDir() );
        $chmods[] = array( "ga+rw" , $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );
        foreach( $chmods as $mod ) {
			$this->logger->info( "{$mod[0]} {$mod[1]}", 1 );
            system("chmod -R {$mod[0]} {$mod[1]}");
        }

    }
}

