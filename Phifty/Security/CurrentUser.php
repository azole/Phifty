<?php
namespace Phifty\Security;
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
 *
 *   $currentUser = new CurrentUser(array( 
 *       'model_class' => 'User\Model\User',
 *   ));
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
            if( isset($args['record']) ) {
                $record = $args['record'];
                $this->userModelClass = get_class($record);
            }
            else {
                $this->userModelClass = 
                    isset($args['model_class']) 
                        ? $args['model_class']
                        : kernel()->config->get( 'framework', 'CurrentUser.Model' ) 
                            ?: 'User\Model\User';  // default user model (User\Model\User)
            }

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
            // load record from session, 
            // get current user record id, and find record from it.
            // 
            // TODO: use virtual loading, do not manipulate database if we have 
            // data in session already.
            //
            // TODO: provide a verify option to verify database item before 
            // loading.
            if( $userId = $this->session->get( $this->primaryKey ) ) {
                $class = $this->userModelClass;
                $virtualRecord = new $class;
                foreach( $virtualRecord->getColumnNames() as $name ) {
                    $virtualRecord->$name = $this->session->get($name);
                }
                $this->record = $virtualRecord;
                // $this->setRecord(new $this->userModelClass(array( $this->primaryKey => $userId )));
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
     * Reload record and update session
     */
    public function updateSession()
    {
        if( ! $this->record ) {
            throw new Exception("Record is empty, Can not update session.");
        }
        $this->record->reload();
        $this->updateSessionFromRecord($this->record);
    }

    /**
     * Update session data from record
     *
     * @param mixed User record object
     */
    public function updateSessionFromRecord($record) 
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
            $this->updateSessionFromRecord($record);
            $this->record = $record;
            return true;
        }
        return false;
    }


    public function getRecord() 
    {
        if( $this->record && $this->record->id ) {
            return $this->record;
        }
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

    function __isset($key) 
    {
        return $this->session->has($key) 
             || ($this->record && $this->record->__isset($key));
    }

    /**
     * Integrate getter with model record object
     */
    function __get( $key )
    {
        if($val = $this->session->get($key)) {
            return $val;
        }
        if( $this->record ) {
            return $this->record->$key;
        }
        // throw new Exception('CurrentUser Record is undefined.');
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
        return $this->id; // call __get
    }

    public function logout()
    {
        $this->session->clear();
    }

    /*******************
     * Helper functions 
     *******************/

    // XXX: should be integrated with ACL
    public function isLogged() 
    {
        return $this->getId();
    }

    public function isLogin() {  
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

