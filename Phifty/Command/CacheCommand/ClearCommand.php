<?php
namespace Phifty\Command\CacheCommand;
use CLIFramework\Command;

class ClearCommand extends Command
{
    function brief() {  }

    function execute($args)
    {
        $logger = $this->getLogger();

        $logger->info( 'Cleaning up cache...' );

        $bs = webapp()->cache->getBackends();
        foreach( $bs as $b ) {
            $b->clear();
        }
        $logger->info( 'Done' );
    }
}

