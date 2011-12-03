<?php
namespace Phifty\Command;

use Phifty\FileUtils;
use Phifty\CodeTemplate;

class AddTest extends \Phifty\Command
{
    public $longOpts = array( 
        'v|verbose',
        'd|dir:'
    );

    function run()
    {
        $testDir = 'tests';
        $testName = $this->getArg(2);
        $options = $this->getOptions();

        if( $options->dir ) {
            FileUtils::mkpath( $options->dir );
            $testDir = $options->dir;
        }

        // create a plugin 
        if( preg_match( '/\W/', $testName ) ) {
            die( "Invalid test name: a-z, A-Z, 0-9 please" );
        }



        $testFilePath = FileUtils::path_join( $testDir , $testName . "Test.php" );

        if( ! file_exists( $testDir ) ) 
            mkdir( $testDir );

        if( 0 && file_exists( $testFilePath ) )
            $this->doExit( "Test file already created." );

        $this->log("Creating unit test file: $testFilePath");

        $codetpl = new CodeTemplate();
        if( $codetpl->hasTemplate( 'test.php' ) ) {
            $text = $codetpl->render(  'test.php' , array('TestName' => $testName ));
            if( $text )
                file_put_contents( $testFilePath , $text );
        }

        $this->log( 'Running phpunit ' . $testFilePath );
        system( "phpunit $testFilePath" );

        $this->log( 'Unit test file created.' );

        # check if dir contains .git dir
        $st = $this->pExecute( "git status" );
        if( preg_match( '/On branch/i' , @$st['stdout'] ) ) {
            $this->execute( "git add -v $testFilePath" );
        }

    }

}

?>
