<?php



namespace Phifty\Command;

class Apc extends \Phifty\Command
{

    function run()
    {
        $subcommand = $this->getArg(0 + 2);
        switch( $subcommand )
        {
            case "clear":
				$this->log( "Clearing APC cache..." );
                apc_clear_cache();
				break;
        }
    }

}



?>
