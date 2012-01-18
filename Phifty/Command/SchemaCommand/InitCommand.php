<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\Command\SchemaCommand;

use Phifty\Schema\Manager;
use Phifty\FileUtils;
use Phifty\AppClassKit;
use CLIFramework\Command;

class InitCommand extends Command
{
    function brief()
    {
        return 'init schema';
    }

    function options($opts)
    {
        $longOpts = array(
            'u|user:',
            'p|pass:',
            'g|grant:',
            'M|model:',
            'grant-on:',
            'drop-user:',
            'list-user'
        );
    }

    function execute()
    {
        $args = func_get_args();
        $opts = $this->getOptions();

        $logger = $this->getLogger();
        $logger->info( 'Running schema initialization script...' );

        $kernel = webapp();
        $dbname = $kernel->config('database.config.dbname');
        $config     = $kernel->config('database');

        $driver     = $config->driver;
        $dbConfig   = $config->config;
        $connConfig = @$config->conn;
        $connConfig['no_select_db'] = 1;


        $logger->info("Connecting to database $dbname ...");
        $db = \LazyRecord\Engine::getInstance();
        $conn = $db->connect( $driver , $dbConfig , $connConfig ); 

        $manager = new \Phifty\Schema\Manager( $db );
        $hasDb = $manager->hasDb( $dbname );

        if( $hasDb ) {
            $logger->info("Found existing database $dbname");
            $manager->useDb( $dbname );
        } else {
            $logger->info("Creating database $dbname");
            $manager->createAndUseDb($dbname);
        }

        // build for specified models
        if( count($args) > 0 ) {
            $sql = $manager->initModels($args);
            $logger->info( $sql );
        }
        else {
            $sql = $manager->initModels();
            $logger->info( $sql );
        }

        $logger->info('Done');
    }

    /*
    function dbDrop( $manager , $dbname )
    {
        $this->log("Dropping database $dbname.");
        $manager->conn->query( "DROP DATABASE IF EXISTS $dbname" );
        $warns = $manager->conn->handle()->get_warnings();
        if( $warns ) {
            print_r( $warns );
            return;
        }
    }
    */

    /*
	function execute($arguments)
	{
		// connect to database
		// $dbc = $kernel->db()->connection();
        try {
            if( @$opts->rebuild ) 
            {
                $this->dbDrop( $manager , $dbname );
                $manager->createAndUseDb($dbname);
                $sql = $manager->initModels();
                print_r($sql);
            }
            elseif( @$opts->drop )
            {
                $this->dbDrop( $manager , $dbname );
            }

            if( @$opts->user )
            {
                $this->log( "Creating user $opts->user ..." );
                $manager->createUser( $opts->user , $opts->pass );
            }
            elseif( @$opts->drop_user ) 
            {
                $this->log( "Dropping user $opts->drop_user ..." );
                $manager->dropUser( $opts->drop_user );
            }
            elseif( @$opts->list_user )
            {
                $users = $manager->users();
                foreach( $users as $user ) {
                    $this->log( 
                        sprintf( "%30s %s" ,
                        $user->user . '@' . $user->host,$user->password ));
                }
            }

        }
        catch( \Exception $e ) 
        {
            echo "Exception: " . $e->getMessage();
        }
	}
     */
}

