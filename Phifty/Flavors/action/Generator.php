<?php
namespace action;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    function brief() { return 'generate action class'; }

    function generate($ns,$actionName)
    {
        $app = kernel()->app($ns) ?: kernel()->plugin($ns);
        if( ! $app ) {
            throw new Exception("$ns application or plugin not found.");
        }

        $this->logger->info("Found $ns");

        $dir = $app->locate();
        $className = $ns . '\\Action\\' . $actionName;
        $actionDir = $dir . DIRECTORY_SEPARATOR . 'Action';
        $classFile = $actionDir . DIRECTORY_SEPARATOR . $actionName . '.php';

        if( ! file_exists($actionDir) ) {
            mkdir($actionDir, 0755, true);
        }

        if( file_exists($classFile) ) {
            $this->logger->info("Found existing $classFile, skip");
            return;
        }

        $this->render('Action.php.twig',$classFile,array( 
            'namespace' => $ns,
            'actionName' => $actionName,
        ));
    }
}
