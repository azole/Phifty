<?php
namespace crud_template;
use GenPHP\Flavor\BaseGenerator;
use Exception;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate controller class'; }

    public function generate($ns,$crudId)
    {
        $bundle = kernel()->app($ns) ?: kernel()->bundle($ns,true);
        if (! $bundle) {
            throw new Exception("$ns application or plugin not found.");
        }
        $templateDir = $bundle->getTemplateDir() . DIRECTORY_SEPARATOR . $crudId;
        $this->copyDir( 'template' , $templateDir );
    }

}
