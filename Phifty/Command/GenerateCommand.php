<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\CodeTemplate;
use CLIFramework\Command;

class GenerateCommand extends Command
{
    function brief() { return 'template generator command'; }

    function init()
    {
        $this->registerCommand('action','Phifty\Command\Generate\GenerateActionCommand');
        $this->registerCommand('model','Phifty\Command\Generate\GenerateModelCommand');
        $this->registerCommand('controller','Phifty\Command\Generate\GenerateControllerCommand');
        $this->registerCommand('test','Phifty\Command\Generate\GenerateTestCommand');
    }
}

