<?php
namespace Phifty;
use Phifty\Session;
use Exception;

/**
 * @package Phifty
 *
 * Phifty CurrentUser object
 *
 * managing current user data stash, you can 
 * define your custom user model and your custom current user class
 * to customize this.
 *
 * This class is mixined with current user model class.
 *
 * TODO: support login from cookie
 *
 *   $currentUser = new CurrentUser;  // load current user from session data
*/
class CurrentUser 
{
    /* User model class */
    public $userModelClass;

    /**
     * @var mixed User model record
     */
    public $record; // user model record


    /**
     * @var string session prefix string
     */
    public $sessionPrefix = '__user_';

    /**
     * @var Phifty\Session Session Manager
     */
    public $session;

    function __construct($record = null)
    {
        $this->userModelClass = kernel()->config->get( 'framework', 'CurrentUser.Model' );

        /* create a session pool with prefix 'user_' */
        $this->session = new Session( $this->sessionPrefix );

        /* if record is specified, update session from record */
        if( $record ) {
            if( ! $this->setRecord( $record ) ) {
                throw new Exception('CurrentUser can not be loaded from record.');
            }
        } else {
            // load from session, 
            // get current user record id, and find record from it.
            if( $userId = $this->session->id ) {
                $this->setRecord(new $this->userModelClass($userId));
            }
        }
    }

    public function setUserModelClass( $class )
    {
        $this->userModelClass = $class;
    }


    public function __set( $key , $value )
    {
        $this->session->set($key, $value);
    }

    public function __get( $key )
    {
        return $this->session->get($key);
    }

    public function updateSession($record) 
    {
        $columns = $record->getColumnNames();
        foreach( $columns as $name ) {
            $this->session->set( $name, $record->$name );
        }
    }

    public function setRecord( $record )
    {
        if( $record && $record->id ) {
            $this->updateSession($record);
            $this->record = $record;
            return true;
        }
        return false;
    }

    public function getId()
    {
        return $this->session->id;
    }

    public function getRole()
    {
        return $this->session->role; // this will retrieve data from $this->data
    }

    public function getCurrentRecord() 
    {
        if( $this->record )
            return $this->record;
        return false;
    }

    public function currentName()
    {
        if( $u = $this->getCurrentRecord() ) {
            if( method_exists( $u , 'currentName' ) )
                return $u->currentName();
        }
        return $this->getId();
    }

    public function logout()
    {
        $this->session->clear();
    }

    /*******************
     * Helper functions 
     *******************/

    /* is logged in ? */
    function isLogged() 
    {
        return $this->getId();
    }

    function isAdmin() 
    {
        return $this->role === "admin";
    }

    function isStaff()
    {
        return $this->role === "staff";
    }

    function isUser() 
    {
        return $this->role === "user";
    }

}

