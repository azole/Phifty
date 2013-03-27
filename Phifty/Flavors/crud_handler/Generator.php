<?php
namespace crud_handler;
use GenPHP\Flavor\BaseGenerator;
use Exception;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate CRUDHandler class'; }

    public function generate($ns,$modelName,$crudId)
    {
        $bundle = kernel()->app($ns) ?: kernel()->plugin($ns);
        if (! $bundle) {
            throw new Exception("$ns application or plugin not found.");
        }

        $bundleName = $bundle->getNamespace();
        $model = $bundle->getModel($modelName);
        $modelClass = get_class($model);

        $handlerClass = $modelName . 'CRUDHandler';
        $classFile = $bundle->locate() . DIRECTORY_SEPARATOR . $handlerClass . '.php';

        $this->render('CRUDHandler.php.twig',$classFile,array(
            'handlerClass' => $handlerClass,
            'bundleName'   => $bundleName,
            'modelClass'   => $modelClass,
            'model'        => $model,
            'crudId'       => $crudId,
        ));
    }
}
