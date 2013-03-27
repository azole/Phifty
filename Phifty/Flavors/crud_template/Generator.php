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
        if (! $app) {
            throw new Exception("$ns application or plugin not found.");
        }
        $templateDir = $app->getTemplateDir() . DIRECTORY_SEPARATOR . $crudId;
        $this->copyDir( 'template' , $templateDir );
    }

}
