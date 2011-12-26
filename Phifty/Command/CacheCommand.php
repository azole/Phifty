<?php
namespace Phifty\Command;
use CLIFramework\Command;

class CacheCommand extends Command
{
    function init()
    {
        $this->registerCommand('clear');
    }

    function execute($args)
    {
    }
}
