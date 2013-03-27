<?php
namespace crud_handler;
use GenPHP\Flavor\BaseGenerator;
use Exception;
use Phifty\Inflector;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate CRUDHandler class'; }

    public function generate($ns,$modelName,$crudId = null)
    {
        $bundle = kernel()->app($ns) ?: kernel()->plugin($ns) ?: kernel()->plugins->load($ns);
        if (! $bundle) {
            throw new Exception("$ns application or plugin not found.");
        }

        if ( ! $crudId ) {
            $crudId = Inflector::underscore($modelName);
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
