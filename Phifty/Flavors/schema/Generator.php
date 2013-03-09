<?php
namespace schema;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate schema class'; }

    public function generate($ns,$schemaName)
    {
        $app = kernel()->app($ns) ?: kernel()->plugin($ns);
        if (! $app) {
            throw new Exception("$ns application or plugin not found.");
        }

        $this->logger->info("Found $ns");

        if ( strrpos($schemaName,'Schema') === false ) {
            $schemaName .= 'Schema';
        }

        $args = func_get_args();
        $args = array_splice($args,2);
        $schemaColumns = array();
        foreach ($args as $arg) {
            $list = explode(':',$arg);
            $schemaColumns[] = array('name' => $list[0], 'type' => $list[1], 'var' => @$list[2]);
        }

        $dir = $app->locate();
        $className = $ns . '\\Schema\\' . $schemaName;
        $classDir = $dir . DIRECTORY_SEPARATOR . 'Schema';
        $classFile = $classDir . DIRECTORY_SEPARATOR . $schemaName . '.php';

        if ( ! file_exists($classDir) ) {
            mkdir($classDir, 0755, true);
        }

        if ( file_exists($classFile) ) {
            $this->logger->info("Found existing $classFile, skip");

            return;
        }

        $this->render('Schema.php.twig',$classFile,array(
            'namespace' => $ns,
            'schemaName' => $schemaName,
            'schemaColumns' => $schemaColumns,
        ));
    }

}
