<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\Plugin\Plugin;
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
        return 'export application/plugin web paths to webroot/.';
    }

    public function execute()
    {
        $options = $this->options;
        $kernel       = kernel();
        $webroot      = $kernel->webroot;
        $this->logger->info( "Exporting web directory to webroot..." );

        /* Make directories */
        $dirs = array();
        $dirs[] = $webroot;
        $dirs[] = $webPluginDir;

        $dirs[] = $webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';
        foreach ( $dirs as $dir )
            FileUtils::mkpath( $dir , true );

        system('chmod -R og+rw ' . $webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );

        foreach ( kernel()->plugins as $plugin ) {

        }
        $this->logger->info( "Done" );
    }
}
