<?php
namespace Phifty;
use Phifty\Session;
use Exception;
use BadMethodCallException;

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
     * @var string model primary key
     */
    public $primaryKey = 'id';

    /**
     * @var string session prefix string
     */
    public $sessionPrefix = '__user_';

    /**
     * @var Phifty\Session Session Manager
     */
    public $session;

    function __construct($args = array() )
    {
        $record = null;
        if( is_object($args) ) {
            $record = $args;
        } 
        else {
            $this->userModelClass = 
                isset($args['model_class']) 
                ? $args['model_class']
                : kernel()->config->get( 'framework', 'CurrentUser.Model' );

            if( isset($args['session_prefix']) ) {
                $this->sessionPrefix = $args['session_prefix'];
            }
            if( isset($args['primary_key']) ) {
                $this->primaryKey = $args['primary_key'];
            }
        }

        /**
         * Initialize a session pool with prefix 'user_' 
         */
        $this->session = new Session( $this->sessionPrefix );

        /* if record is specified, update session from record */
        if( $record ) {
            if( ! $this->setRecord( $record ) ) {
                throw new Exception('CurrentUser can not be loaded from record.');
            }
        } else {
            // load from session, 
            // get current user record id, and find record from it.
            if( $userId = $this->session->get( $this->primaryKey ) ) {
                $this->setRecord(new $this->userModelClass(array( $this->primaryKey => $userId )));
            }
        }
    }


    /**
     * Set user model class
     *
     * @param string $class user model class
     */
    public function setUserModelClass($class)
    {
        $this->userModelClass = $class;
    }



    /**
     * Update session data from record
     *
     * @param mixed User record object
     */
    public function updateSession($record) 
    {
        foreach( $record->getColumnNames() as $name ) {
            $this->session->set( $name, $record->$name );
        }
    }


    /**
     * Set current user record 
     *
     * @param mixed User record object
     *
     * @return bool
     */
    public function setRecord( $record )
    {
        if( $record && $record->id ) {
            $this->updateSession($record);
            $this->record = $record;
            return true;
        }
        return false;
    }



    /**
     * Integrate setter with model record object
     */
    public function __set( $key , $value )
    {
        if( $this->record ) {
            $this->record->update(array($key => $value));
            $this->session->set($key, $value);
        }
    }

    /**
     * Integrate getter with model record object
     */
    public function __get( $key )
    {
        if( $this->record ) {
            return $this->record->$key;
            // return $this->session->get($key);
        }
    }


    /**
     * Mixin with user record object.
     */
    public function __call($method,$args) {
        if( method_exists($this->record,$method) ) {
            return call_user_func_array(array($this->record,$method), $args);
        }
        else {
            throw new BadMethodCallException("$method not found.");
        }
    }


    public function getId()
    {
        return $this->session->id;
    }

    public function getRole()
    {
        return $this->session->role; // this will retrieve data from $this->data
    }

    public function logout()
    {
        $this->session->clear();
    }

    /*******************
     * Helper functions 
     *******************/

    /* is logged in ? */
    public function isLogged() 
    {
        return $this->getId();
    }

    public function isAdmin() 
    {
        return $this->role === "admin";
    }

    public function isStaff()
    {
        return $this->role === "staff";
    }

    public function isUser() 
    {
        return $this->role === "user";
    }
}

