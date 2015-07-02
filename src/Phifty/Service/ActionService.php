<?php
namespace Phifty\Service;
use Exception;
use ActionKit\ActionRunner;
use ActionKit\ServiceContainer;
use ActionKit\ActionTemplate\TwigActionTemplate;
use ActionKit\ActionTemplate\CodeGenActionTemplate;
use ActionKit\ActionTemplate\RecordActionTemplate;
use ActionKit\ActionTemplate\UpdateOrderingRecordActionTemplate;

class ActionService
    implements ServiceRegister
{
    public function getId() { return 'action'; }

    public function register($kernel, $options = array() )
    {
        $container = new ServiceContainer;
        $generator = $container['generator'];
        $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate);
        $generator->registerTemplate('CodeGenActionTemplate', new CodeGenActionTemplate);
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate);
        $generator->registerTemplate('UpdateOrderingRecordActionTemplate', new UpdateOrderingRecordActionTemplate);

        $action = new ActionRunner($container);
        $action->registerAutoloader();

        $kernel->action = function() use ($options,$action) {
            return $action;
        };

        $kernel->event->register('view.init', function($view) use ($action) {
            $view->args['Action'] = $action;
        });

        $kernel->event->register('phifty.before_path_dispatch',function() use ($kernel) {
            // check if there is $_POST['__action'] or $_GET['__action']
            if ( ! isset($_REQUEST['__action']) ) {
                return;
            }

            $runner = $kernel->action;  // get runner
            $kernel->event->trigger('phifty.before_action');
            $strout = fopen("php://stdout", "w");
            $result = $runner->handleWith($strout, $_REQUEST, $_FILES);
        });
    }
}
