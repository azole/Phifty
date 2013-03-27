<?php
namespace controller;
use GenPHP\Flavor\BaseGenerator;
use Exception;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate controller class'; }

    public function generate($ns,$crudId)
    {
        $app = kernel()->app($ns) ?: kernel()->plugin($ns);
        if (! $app) {
            throw new Exception("$ns application or plugin not found.");
        }
        $templateDir = $app->getTemplateDir() . DIRECTORY_SEPARATOR . $crudId;
        $this->copy( 'template' , $templateDir );
    }

}
