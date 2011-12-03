<?php
namespace Phifty;

class Commander
{
	public $argv;
	public $script;




	function __construct()
	{

        global $argv;
		$this->argv = array_merge( array() , $argv );

		/* this will always copy array */
		$new_argv = array_merge( array() , $argv );
		$this->script = array_shift( $new_argv );
		$cmd = array_shift( $new_argv );


        $alias = $this->getAlias();
        if( isset($alias[ $cmd ] ) )
            $cmd = $alias[ $cmd ];

        // translate '-' to '_'
        $this->command = preg_replace( '/[-]+/i' , '_' , strtolower($cmd) ); 

            
	}

	function getSubcommand($cmdName)
	{
		$cmdClass = "\\Phifty\\Command\\" . ucfirst( $cmdName );
		spl_autoload_call( $cmdClass );

		if( class_exists( $cmdClass ) ) {
			return new $cmdClass(); /* should always deeply copy array */
        } else {
            die( $cmdClass . ' not found.'  );
        }
		return null;
	}

	function run()
	{

		if( $this->command ) {
			$cmd = $this->getSubcommand( $this->command );
			if( $cmd ) {
				$cmd->run();
			} else {
				die( "Command '$this->command' not found.");
			}
		} 
		else {
			$this->defaultRun();
		}
	}

	function help()
	{
		/* print help message .... */
	}

	function defaultRun()
	{

	}


    function getAlias()
    {
        return array(
            "gen" => "generate"
        );
    }
}




?>
