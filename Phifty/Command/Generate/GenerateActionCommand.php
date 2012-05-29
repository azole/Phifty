<?php
namespace Phifty\Command\Generate;
use CLIFramework\Command;

class GenerateActionCommand extends Command
{

    function brief() { return 'generate action class'; }

    function usage() { return '[application name|plugin name] [action name]'; }

    function execute($ns,$actionName) 
    {
        if( $app = kernel()->app($ns) ) { } 
        elseif( $app = kernel()->plugin($ns) ) { }

        $dir = $app->locate();
        $className = $ns . '\\Action\\' . $actionName;

        kernel()->action->
        $gen = new ActionGenerator(array( 'cache' => true ));
    }



}

