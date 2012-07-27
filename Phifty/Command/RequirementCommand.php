<?php
namespace Phifty\Command;
use CLIFramework\Command;

class RequirementCommand extends Command
{
    function printResult($msg, $ok) {
        printf("% -30s %s\n",$msg . ':', $ok ? 'ok' : 'not ok' );
    }

    function execute() 
    {
        // xxx: Can use universal requirement checker.
        //
        // $req = new Universal\Requirement\Requirement;
        // $req->extensions( 'apc','mbstring' );
        // $req->classes( 'ClassName' , 'ClassName2' );
        // $req->functions( 'func1' , 'func2' , 'function3' )
        $this->printResult('reflection', class_exists('ReflectionObject') );

        $kernel = kernel();
        if( $configext = $kernel->config->get('Requirement.Extensions') ) {
            foreach( $configext as $extname ) {
                $ret = extension_loaded( $extname );
                $this->printResult("$extname extension", $ret );
            }
        }


        // TODO: 
        //   1. get services and get dependencies from these services for checking

    }
}


