<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Roller\Dumper\ConsoleDumper;

class RouterCommand extends Command
{

    function brief() { return 'List router'; }
    
    function execute()
    {
        $router = kernel()->router;
        $router->compile();

        $dumper = new ConsoleDumper;
        $dumper->dump( $router->routes );
    }
}


