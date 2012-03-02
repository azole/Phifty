<?php
namespace Phifty;

// vim:fdm=marker:
// Action {{{
/*

	$a = new Action( .. parameters ... )
	$a->run();

	$rs = $a->result();


Step1

class ...

	function schema() {
		$this->param( 'username' )
			->label( _('Username') )
			->useSuggestion();

		$this->param( 'password' )
			->useValidator();

		$this->param( 'country' )
			->useCompleter();
	}

	function validatePassword( $value , $args ) {

		return $this->valid( $message );

		# or
		return $this->invalid( $message );
	}

	function suggestUsername( $value , $args ) {
		return;  # not to suggest
		return $this->suggest( "$value is used. use: " , array( ... ) );
	}

	function completeCountry( $value , $args ) {

		...
	}

 */
abstract class Action 
{
	public $currentUser;
	public $args = array();   // post,get args for action
	public $result; // action result
	protected $params = array(); // parameter column objects

    function __construct( $args = array() , $currentUser = null ) 
    {
        $this->args = $args;
		$this->result = new \Phifty\Action\Result;
        if( $currentUser )
            $this->currentUser = $currentUser;
		$this->schema();
        $this->result->args( $this->args );
        $this->init();
	}

    function getFileArg( $name )
    {

    }


    /* new methods */
    function runSanitize() 
    {
        foreach( $this->params as $key => $column ) {
            if( isset($this->args[$key] ) )
                $this->args[$key] = $column->sanitize( $this->args[ $key ] );
        }
    }

    protected function validateParam( $name )
    {
        if( $name == '__ajax_request' )
            return;

        if( ! isset($this->params[ $name ] ) ) {
            $this->result->addValidation( $name, array( 'invalid' => "Contains invalid arguments: $name" ));
            return true;
        }

        $param = $this->params[ $name ];
        $ret = (array) $param->validate( @$this->args[$name] );
        if( is_array($ret) ) {
            if( $ret[0] ) {
                # $this->result->addValidation( $name, array( "valid" => $ret[1] ));
            } else {
                $this->result->addValidation( $name, array( 'invalid' => @$ret[1] ));
                return true;
            }
        } else {
            throw new \Exception("Unknown validate return value of $name => " . $this->getName() );
        }
        return false;
    }

    function runValidate()
    {
        /* it's different behavior when running validation for create,update,delete,
         *
         * for generic action, just traverse all params. */
        $error = false;
        foreach( $this->params as $name => $param ) 
        {
            $hasError = $this->validateParam( $name );
            if( $hasError )
                $error = true;
        }

        if( $error )
            $this->result->error( _('Validation Error') );
        return $error;
    }

    function runPreinit()
    {
        foreach( $this->params as $key => $param ) {
            $param->preinit( $this->args );
        }
    }

    function runInit()
    {
        foreach( $this->params as $key => $param ) {
            $param->init( $this->args );
        }
    }


    function __invoke() 
    {
        /* run column methods */
        // XXX: merge them all...
        $this->runPreinit();
        $this->runSanitize();
        $error = $this->runValidate();
        if( $error )
            return false;

        $this->runInit();

        $this->beforeRun();
        $this->run();
        $this->afterRun();
    }


    /* **** value getters **** */
    function getClass() { return get_class($this); }
    function getName()
    {
        $class = $this->getClass();
        $pos = strpos( $class, '::Action::' );
        return substr( $class , $pos + strlen('::Action::') );
    }

    function params() 
    {
        return $this->params;
    }


    function getParam( $field ) 
    {
        return @$this->params[ $field ];
    }

    function hasParam( $field ) 
    {
        return @$this->params[ $field ] ? true : false; 
    }


    function isAjax()
    {  
        return isset( $_REQUEST['__ajax_request'] );
    }

    function getCurrentUser() 
    {
        if( $this->currentUser )
            return $this->currentUser;
    }

    function setCurrentUser( $user ) 
    {
        $this->currentUser = $user;
    }


    function currentUserCan( $user ) 
    {
        return $this->record->currentUserCan( $this->type , $this->args , $user );
    }

    function arg( $name ) 
    {
        return @$this->args[ $name ]; 
    }

    function getArgs() 
    {
        return $this->args; 
    }

    function getFile( $name )
    {
        return @$_FILES[ $name ];
    }

    function getFiles() 
    {
        return @$_FILES;
    }


    function setArg($name,$value) 
    { 
        $this->args[ $name ] = $value ; 
        return $this; 
    }

    function setArgs($args) 
    { 
        $this->args = $args;
        return $this; 
    }

    function param( $name , $type = null ) 
    {
        if( $type ) {
            $cls = '\Phifty\Action\Column\\' . $type;
            return $this->params[ $name ] = new $cls( $name , $this );
        }
        else {
            // default column
            return $this->params[ $name ] = new \Phifty\Action\Column( $name , $this );
        }
	}

    function schema() 
    {

    }

    function init()
    {

    }

    function error( $message ) { 
        $this->result->error( $message );
        return false;
    }

    function addData( $key , $val )
    {
        $this->result->addData( $key , $val );
    }

	function success( $message , $data = null ) {
        $this->result->success( $message );
        if( $data )
            $this->result->mergeData( $data );
        return true;
	}

    function beforeRun() {  }

    function afterRun()  {  }

	/* run */
    function run() 
    {
        return true;
    }


	/* complete field */
	public function complete( $field ) {
		$param = $this->getParam( $field );
		if( ! $param )
			die( 'action param not found.' );
		$ret = $param->complete();

		if( ! is_array( $ret ) )
			throw new Exception( "Completer doesnt return array. [type,list]\n" );

		// [ type , list ]
		$this->result->completion( $field , $ret[0], $ret[1] );
	}

    public function getResult() 
    {
        return $this->result; 
    }

    public function redirect( $path ) {

        /* for ajax request, we should redirect by json result,
         * for normal post, we should redirect directly. */
        if( $this->isAjax() ) {
            $this->result->redirect( $path );
            return;
        }
        else {
            header( 'Location: ' . $path );
            exit(0);
        }
    }

    public function redirectLater( $path , $secs = 1 )
    {
        if( $this->isAjax() ) {
            // XXX: more support.
            $this->result->redirect( $path );
            return;
        } else {
            header("Refresh: $secs; url=$path");
        }
    }

    public function renderWidget( $name , $type , $attrs = array() )
    {
        $param = $this->getParam( $name );
        return $param->renderWidget( $type, $attrs );
    }

    public function render( $name = null , $attrs = array() ) 
    {
        if( $name ) {
            $param = $this->getParam( $name );
            return $param->render( $attrs );
        }
        else {
            /* render all */
            $html = '';
            foreach( $this->params as $param ) {
                $html .= $param->render( $attrs );
            }
            return $html;
        }
    }

}

?>
