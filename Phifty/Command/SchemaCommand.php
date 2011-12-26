<?php
namespace Phifty\Command;
use Phifty\Schema\Manager;
use Phifty\FileUtils;
use Phifty\AppClassKit;
use CLIFramework\Command;

class SchemaCommand extends Command
{
    function brief()
    {
        return 'schema command';
    }

    function init()
    {
        $this->registerCommand( 'init' );
    }

    function options($opts)
    {
        $longOpts = array(
            'drop',
            'init',
            'rebuild',
            'u|user:',
            'p|pass:',
            'g|grant:',
            'M|model:',
            'grant-on:',
            'drop-user:',
            'list-user'
        );
    }

    function execute($arguments)
    {
    }

}
