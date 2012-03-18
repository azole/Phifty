<?php
namespace Phifty;


/*
	For users that needs to be remembered,
	should auth key be setted in Cookie.

	when user back,
	first the auth key in cookie.
	if matched to a user, then do the login
*/
class CurrentUser 
{
    /* User model class */
	public $userModelClass;

	public $record; // user model record

	public $sessionPrefix = '__user_';

    /* Phifty\Session object */
	public $session;

	function __construct( $record = null ) 
	{
        $this->userModelClass = webapp()->config->get( 'current_user.model' );

        /* create a session pool with prefix 'user_' */
		$this->session = new \Phifty\Session( $this->sessionPrefix );

		if( $record !== null )
			$this->loadFromRecord( $record );
   	}

	function setUserModelClass( $class )
	{
		$this->userModelClass = $class;
	}


	function __set( $key , $value )
	{
		$this->session->set($key, $value);
	}

	function __get( $key )
	{
		return $this->session->get($key);
	}

	function loadFromRecord( $record )
	{
		if( $record == null )
			return false;

		/* is not loaded */
		if( ! $record->id ) 
			return false;

		$columns = $record->getColumnNames();
		foreach( $columns as $name ) {
			$this->session->set( $name, $record->$name );
        }
        $this->record = $record;
		return true;
	}

	function getId() 
	{
		return $this->session->id;
	}

	function getRole()
	{
		return $this->session->role; // this will retrieve data from $this->data
	}

	function newUserModel()
	{
		$class = $this->userModelClass;
		return new $class;
	}

	function getModelColumns() 
	{
		$user = $this->newUserModel();
		$names = $user->getColumnNames();
		return $names;
    }

	function getCurrentRecord() 
	{
		if( $this->record )
			return $this->record;

		if( $this->getId() ) {
			$user = $this->newUserModel( (int) $this->getId() );
			if( $user->id )
				return $this->record = $user;
            else
                throw new \Exception( 'CurrentUser data not found.' );
		}
		return null;
	}

	function currentName()
	{
		$u = $this->getCurrentRecord();
		if( $u ) {
			if( method_exists( $u , 'currentName' ) )
				return $u->currentName();
		}
		return $this->getId();
	}

	function logout()
	{
		$this->session->clear();
	}

	/* helper functions */

    /* is logged in ? */
	function isLogged() 
	{
		return $this->getId();
    }

	function isAdmin() 
	{
		return $this->role == "admin";
	}

	function isStaff()
	{
		return $this->role == "staff";
	}

	function isUser() 
	{
		return $this->role == "user";
	}

}

?>
