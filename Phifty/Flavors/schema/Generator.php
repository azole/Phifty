<?php
namespace schema;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    function brief() { return 'generate schema class'; }

    function generate($ns,$schemaName)
    {
        $app = kernel()->app($ns) ?: kernel()->plugin($ns);
        if( ! $app ) {
            throw new Exception("$ns application or plugin not found.");
        }

        $this->logger->info("Found $ns");


        if( strrpos($schemaName,'Schema') === false ) {
            $schemaName .= 'Schema';
        }

        $args = func_get_args();
        $args = array_splice($args,2);
        $schemaActions = array('indexAction');
        foreach( $args as $arg ) {
            $schemaActions[] = $arg . 'Action';
        }

        $dir = $app->locate();
        $className = $ns . '\\Schema\\' . $schemaName;
        $classDir = $dir . DIRECTORY_SEPARATOR . 'Schema';
        $classFile = $classDir . DIRECTORY_SEPARATOR . $schemaName . '.php';

        if( ! file_exists($classDir) ) {
            mkdir($classDir, 0755, true);
        }

        if( file_exists($classFile) ) {
            $this->logger->info("Found existing $classFile, skip");
            return;
        }

        $this->render('Schema.php.twig',$classFile,array( 
            'namespace' => $ns,
            'schemaName' => $schemaName,
            // 'schemaColumns' => $schemaActions,
        ));
    }

}
