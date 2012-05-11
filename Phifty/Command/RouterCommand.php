<?php
namespace Phifty\Command;
use CLIFramework\Command;

class RouterCommand extends Command
{

    function brief() { return 'List router'; }
    
    function execute()
    {
        $router = kernel()->router;
        $router->compile();
        $dumper = new \Roller\Dumper\ConsoleDumper;
        $dumper->dump( $router->routes );
    }
}


