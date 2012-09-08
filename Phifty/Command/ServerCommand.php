<?php
namespace Phifty\Command;
use CLIFramework\Command;

class ServerCommand extends Command
{
    function brief() { return 'run http server'; }
    
    function execute()
    {
        $php = $_SERVER['_'];
        chdir(PH_APP_ROOT . DIRECTORY_SEPARATOR . 'webroot');
        pcntl_exec($php, array('-S', 'localhost:8000', 'server.php'));
    }
}

