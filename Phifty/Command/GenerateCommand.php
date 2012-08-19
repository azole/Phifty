<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\CodeTemplate;
use CLIFramework\Command;

class GenerateCommand extends Command
{
    function brief() { return 'template generator command'; }

#      function init()
#      {
#  #          $this->registerCommand('action','Phifty\Command\Generate\GenerateActionCommand');
#  #          $this->registerCommand('model','Phifty\Command\Generate\GenerateModelCommand');
#  #          $this->registerCommand('controller','Phifty\Command\Generate\GenerateControllerCommand');
#  #          $this->registerCommand('test','Phifty\Command\Generate\GenerateTestCommand');
#      }

    function execute($flavor) {
        $args = func_get_args();
        array_shift($args);

        $loader = new \GenPHP\Flavor\FlavorLoader(array( 
            PH_ROOT . '/src/Phifty/Flavors'
        ));
        if( $flavor = $loader->load($flavor) ) {
            $runner = new \GenPHP\GeneratorRunner;
            $runner->run($generator,$args);
        } else {
            throw new Exception("Flavor $flavor not found.");
        }
        $this->info('Done');
    }
}

