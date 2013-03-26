<?php
namespace Phifty\Service;
use Phifty\View\Engine;

/**
 * Usage:
 *
 *    $view = kernel()->view;
 */
class ViewFactory
{

    public $backend = 'twig';
    public $class = 'Phifty\\View';
    public $templateDirs;

    public function __construct()
    {
    }

    public function __invoke($class = null)
    {
        /* get template engine */
        $engine = Engine::createEngine( $this->backend );
        $viewClass = $class ? $class : $this->class;
        $opts = array();
        if ( $this->templateDirs ) {
            $opts['template_dirs'] = $this->templateDirs;
        }
        return new $viewClass($engine, $opts);
    }
}

class ViewService
    implements ServiceInterface
{
    public $options;

    public function getId() { return 'View'; }
    public function register($kernel, $options = array() )
    {
        $this->options = $options;
        $factory = new ViewFactory;
        if ( isset($options['Backend']) ) {
            $factory->backend = $options['Backend'];
        }
        if ( isset($options['Class']) ) {
            $factory->class = $options['Class'];
        }
        if ( isset($options['TemplateDirs']) ) {
            $factory->templateDirs = $options['TemplateDirs'];
        }
        $kernel->registerFactory('view',$factory);
    }
}
