<?php
namespace Phifty\Schema;

use Phifty\FileUtils;
use Symfony\Component\Finder\Finder;
use Phifty\AppClassKit;
use Exception;

class MySQL_WarningException extends Exception
{
    /*
        object(mysqli_warning)#19 (3) {
            ["message"]=> string(47) "Can't create database 'phifty'; database exists"
            ["sqlstate"]=> string(5) "HY000"
            ["errno"]=> int(1007)
        }
    */
    function __construct( $warn ) {
        parent::__construct( $warn->message );
    }

}

class Manager 
{
    public $db;
    public $conn;

    function __construct( $db )
    {
        $this->db = $db;
        $this->conn = $db->connection();
    }

    function setDb( $db )
    {
        $this->db = $db;
    }


    function getDbList()
    {
        $list = array();
        $result = $this->conn->query('show databases;');
        while( $row = $result->fetch_object() ) {
            $list[] = $row->Database;
        }
        $result->close();
        return $list;
    }

    function hasDb( $dbname )
    {
        $dbList = $this->getDbList();
        return in_array( $dbname , $dbList );
    }


    function createAndUseDb( $dbname , $charset = 'utf8' ) 
    {
        if( ! $dbname )
            die('Database name is not specified.');
        $this->createDb( $dbname , $charset );
        $this->useDb( $dbname );
    }

    function initModelFromClass( $class )
    {
        if( ! class_exists( $class ) )
            spl_autoload_call( $class );

        if( ! is_a( $class , 'Phifty\Model' ) )
            return;

        $model = new $class;

        /* should we export model to database ? */
        if( ! $model->export )
            return '';

        $sql = $model->getSchema();

        /* Insert SQL */
        if( $this->conn->query( $sql ) === false ) {
            throw new \Exception( "SQL Error" );
        }

        $model->preinit();
        $model->bootstrap();
        return $sql;
    }

    function initAppModel( $model )
    {
        /* try to load app model file */
        $class = '\\' . webapp()->getAppName() . '\Model\\' . $model;
        if( ! class_exists( $class ) )
            spl_autoload_call( $class );

        if( ! class_exists( $class ) )
            throw new \Exception( "$class model doesn't exist." );

        return $this->initModelFromClass( $class );
    }


    function initModels( $models = null )
    {
        if( $models ) {
            $schemaSQL = '';
            foreach( $models as $model ) {
                if( strpos( $model , '\\' ) === false )
                    $schemaSQL .= $this->initAppModel( $model );
                else
                    $schemaSQL .= $this->initModelFromClass( $model );
            }
            return $schemaSQL;
        }
        else {
            AppClassKit::loadCoreModels();
            AppClassKit::loadAppModels();
            AppClassKit::loadPluginModels();

            $schemaSQL = '';
            $classes = AppClassKit::modelClasses();
            foreach( $classes as $class ) {
                $schemaSQL .= $this->initModelFromClass( $class );
            }
            return $schemaSQL;
        }
    }


    function createDb( $dbname , $charset )
    {
        $this->conn->query( "CREATE DATABASE IF NOT EXISTS $dbname CHARSET $charset" );
        $warns = $this->conn->handle->get_warnings();
        if( $warns )
            throw new MySQL_WarningException( $warns );
    }

    function useDb( $dbname )
    {
        $this->conn->query( "use $dbname ; " );
    }

    function dropDb( $dbname )
    {

        $this->conn->query( "DROP DATABASE IF EXISTS $dbname" );
        $warns = $this->conn->handle()->get_warnings();
        if( $warns )
            throw new MySQL_WarningException( $warns );
    }


    /*
        mysql> create user 'phifty'@'localhost' identified by '123123'; 
        Query OK, 0 rows affected (0.17 sec)

        mysql> GRANT ALL ON phifty.* TO 'phifty'@'localhost';
        Query OK, 0 rows affected (0.07 sec)

        return sql
    */
    function createUser( $user , $pass = null )
    {
        $sql = "CREATE USER $user ";
        if( $pass )
            $sql .= " identified by '$pass'; ";

        $this->conn->handle()->query( $sql );
        $warns = $this->conn->handle()->get_warnings();
        if( $warns )
            throw new MySQL_WarningException( $warns );

        return $sql;
    }

    function dropUser( $user )
    {
        $sql = "DROP USER $user;";

        $this->conn->query( $sql );
        $warns = $this->conn->handle()->get_warnings();
        if( $warns )
            throw new MySQL_WarningException( $warns );
        return $sql;
    }

    /* return mysql users */
    function users()
    {
        $sql = "select host, user, password from mysql.user;";
        $result = $this->conn->query( $sql );
        $warns = $this->conn->handle()->get_warnings();
        if( $warns )
            throw new MySQL_WarningException( $warns );

        $users = array();

        while( $user = $result->fetch_object() ) {
            $users[] = $user;
        }
        return $users;
    }

    /* grant user on */
    function grantUser( $to , $on = null , $privileges = 'ALL PRIVILEGES' ) 
    {
        /* Grant on current database by default */
        if( ! $on )
            $on = '*.*';

        $sql = "GRANT $privileges ON $on TO $to; ";

        $this->conn->query( $sql );
        $warns = $this->conn->handle()->get_warnings();
        if( $warns )
            throw new MySQL_WarningException( $warns );
        return $sql;
    }

}



?>
