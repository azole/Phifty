<?php

namespace Phifty\Command;
use Phifty\Schema\Manager;
use Phifty\FileUtils;
use Phifty\AppClassKit;

/*
 * Options
 *
 *  --drop
 *       drop schema
 *
 *
 *  --user [user@host]
 *  --pass [password]
 *  --grant [priviledges]
 *  --grant-on [ {currentdb}.* (default)]
 *
*/
class Schema extends \Phifty\Command
{

    public $longOpts = array(
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

	function run()
	{

        $kernel = webapp();
        $dbname = $kernel->config('database.config.dbname');
        

        $config     = $kernel->config('database');
        $driver     = $config->driver;
        $dbConfig   = $config->config;
        $connConfig = @$config->conn;
        $connConfig['no_select_db'] = 1;

        $db = new \LazyRecord\Engine;
        $conn = $db->connect( $driver , $dbConfig , $connConfig ); 

        $this->log( "Database: $dbname" );

        $opts = $this->getOptions();

		/* connect to database */
		// $dbc = $kernel->db()->connection();
        $manager = new \Phifty\Schema\Manager( $db );
        $hasDb = $manager->hasDb( $dbname );

        try {

            if( ! $opts->hasOne() ) {

                /* if model name is specified, shift it. */
                $buildModels = null;
                if( count($this->args) > 2 )
                    $buildModels = array_splice( $this->args , 2 );

                if( ! $hasDb ) {
                    $manager->createAndUseDb($dbname);
                } else {
                    $manager->useDb( $dbname );
                    if( ! $buildModels )
                        return;
                }

                $sql = $manager->initModels( $buildModels );
                print_r( $sql );
                return;
            }
            elseif( @$opts->rebuild ) 
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


		/* disconnect */
	}
}

?>
