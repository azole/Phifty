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
        // $this->registerCOmmand( 'create-user' );
    }

    function options($opts)
    {
        $longOpts = array(
#              'drop',
#              'init',
#              'rebuild',
#              'u|user:',
#              'p|pass:',
#              'g|grant:',
#              'M|model:',
#              'drop-user:',
#              'list-user'
        );
    }

    function execute($arguments)
    {

    }

}
