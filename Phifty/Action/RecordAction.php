<?php
namespace Phifty\Action;
use Exception;

/*
    use Phifty\Action\RecordAction;


    # returns CreateRecordAction
    $createAction = RecordAction::generate( 'RecordName' , 'Create' );

    # returns UpdateRecordAction
    $updateAction = RecordAction::generate( 'RecordName' , 'Update' );


    XXX: validation should be built-in in Model

*/
abstract class RecordAction extends \Phifty\Action
{
    public $record; // record schema object
    public $recordClass;
    public $type;  // action type (create,update,delete...)

    function __construct( $args = array(), $record = null, $currentUser = null ) 
    {
        /* run schema , init base action stuff */
        parent::__construct( $args , $currentUser );
        if( ! $this->recordClass ) {
            throw new Exception( sprintf('Record class of "%s" is not specified.' , get_class($this) ));
        }

        // record name is in Camel case
        $class = $this->recordClass;
        $this->record = $record ? $record : new $class;

        if( is_a( $this , '\Phifty\Action\CreateRecordAction' ) ) {
            $this->type = 'create';
        }
        elseif( is_a( $this, '\Phifty\Action\UpdateRecordAction' ) ) {
            $this->type = 'update';
        }
        elseif( is_a( $this, '\Phifty\Action\DeleteRecordAction' ) ) {
            $this->type = 'delete';
        } else {
            throw new Exception( sprintf('Unknown Record Action Type: %s' , get_class($this) ));
        }

        $this->initRecord();
        $this->initRecordColumn();
    }


    /**
     * load record
     */
    function initRecord() 
    {
        if( isset( $this->args['id'] ) && ! $this->record->id ) {
            $this->record->load( $this->args['id'] );
        }
    }

    /**
     * Convert model columns to action columns 
     */
    function initRecordColumn()
    {
        if( $this->record ) {
            foreach( $this->record->getColumns() as $name => $column ) {
                if( ! isset( $this->params[ $name ] ) )
                    $this->params[ $name ] = \Phifty\Action\ColumnConvert::toParam( $column , $this->record );
            }
        }
    }

    // the schema of record action is from record class.
    function schema() 
    {
        /*
        $this->record->actionSchema( $this , $this->getType() );
        */
    }

    function getType() 
    {
        return $this->type;
    }

    function getRecord() 
    {
        return $this->record; 
    }

    function setRecord($record)
    {
        $this->record = $record;
    }

    function currentUserCan( $user )
    {
        return true;
    }

    function convertRecordValidation( $ret ) {
        foreach( $ret as $rs ) {
            if( $rs->ok ) $this->result->addValidation( $rs->field , array( "valid" => $rs->msg )); 
            else          $this->result->addValidation( $rs->field , array( "invalid" => $rs->msg ));
        }
    }


    /**
     * TODO: seperate this to CRUD actions 
     */
    function runUpdateValidate()
    {
        // validate from args 
        $error = false;
        foreach( $this->args as $key => $value ) {
            /* skip action column */
            if( $key == "action" || $key == "__ajax_request" )
                continue;

            $hasError = $this->validateparam( $key );
            if( $hasError )
                $error = true;
        }
        if( $error )
            $this->result->error( _('Validation Error') );
        return $error;
    }


    /* just run throgh all params */
    function runCreateValidate()
    {
        return parent::runValidate();
    }

    function runDeleteValidate()
    {
        if( isset( $this->args['id'] ) )
            return false;
        return true;
    }

    function runValidate()
    {
        if( $this->type == 'delete' )
            return $this->runDeleteValidate();
        elseif( $this->type == 'update' )
            return $this->runUpdateValidate();
        elseif( $this->type == 'create' )
            return $this->runCreateValidate();
        else
            return parent::runValidate();
    }


    # Use cases:
    #
    # When updating user data:
    #   those columns like "role" is not required. but is required when creating.
    #   but please notice that if user empty the field, we shouldn't update

    /*
     * Update:
        email is required.       

        POST:

        { 
            email => ""
        }

        Reject by validation.

        
        POST:

        {
            name => "Jack"
        }

        Validate ok.

        So the action.js should submit all data of a form.
        But let the action to decide the validation.

        If the same column is validated in action.
        We should skip the record validation. (???)


        XXX: not used.

     */
    function validate( $only = true )
    {
        $args = $this->getArgs();
		$error = null;


        # When updating
        # (Record) We dont need to fill all fields for updating.
        # (Update Action) The action params should be all validated.
        #
        # When creating
        # (Record) The arguments for creating record should be validated.
        # (Create Action) require all arguments that are required.

        /*
            Currently, we validate by action params first.
            Then run record validation, and skip those columns that is defined 
            in action schema.

            Should we inherit all record columns to action ?
            When updating record, we only need those columns that we need for updating.
            So the action behavior should be the same.

            If we defined action schema, we should also respect them.
            Should it be overridded ? or just run after / before ?
        */

        foreach( $this->params as $name => $param ) {
            $ret = null;
            $ret = (array) $param->validate( @$args[ $name ] );
            if( ! $ret )
                continue;

            if( ! $ret[0] ) {
                $this->result->addValidation( $name , array( "invalid" => $ret[1] ));
                $error = true;
			}

            if( $only )
                $ret[0] ? $this->result->valid() : $this->result->invalid(); # set result type if we only run validation.
        }
		if( $error )
			return false;

        # XXX: should we run this before param validation?
        #    When login-ing, 
        #    When registering, the password hash is require 
        if( $this->type == "create" ) 
            $args = $this->record->beforeCreate( $args );  
        elseif( $this->type == "update" )
            $args = $this->record->beforeUpdate( $args );
        elseif( $this->type == "delete" )
            $args = $this->record->beforeDelete( $args );

        $v_rs = $this->record->validate( $this->type , $args );
		
        foreach( $v_rs as $rs ) {
            if( $rs->ok ) $this->result->addValidation( $rs->field , array( "valid" => $rs->msg )); 
            else          $this->result->addValidation( $rs->field , array( "invalid" => $rs->msg ));

            if( $rs->ok == false ) $error = true;
            if( $only ) $rs->ok ? $this->result->valid() : $this->result->invalid(); # set result type if we only run validation.
        }

        /*
        foreach( $args as $name => $value ) {
            # let column could be overrided by action param, skip param
            $param = $this->get_param( $name );
            if( $param )
                continue;

            $column = $this->record->get_column( $name );
            if( ! $column )
                continue;

            $result = null;
            $result = $column->validate( @$args[ $name ] );
            if( ! $result )
                continue;

        }
         */

		if( $only ) {
			if( $error ) $this->result->invalid();
			else         $this->result->valid();
		}
		if( $error )     return false;
		return true;
    }


    /*
     * RecordAction::generate( 'PluginName' , 'News' , 'Create' );
     * will generate:
     * PluginName\Action\CreateNews
     */
    static function generate( $ns , $modelName , $type )
    {
        $modelClass  = '\\' . $ns . '\Model\\' . $modelName;
        $actionName  = $type . $modelName;
        $baseAction  = '\Phifty\Action\\' . $type . 'RecordAction';
        $code =<<<CODE
namespace $ns\\Action {
    class $actionName extends $baseAction
    {
        var \$recordClass = '$modelClass';
    }
}
CODE;
        return $code;
    }

    static function createCRUDClass( $recordClass , $type ) 
    {
        /* split class to ns, model name */
        if( preg_match( '/(\w+)\\\Model\\\(\w+)/' , $recordClass , $regs ) ) 
        {
            list( $orig, $ns , $modelName ) = $regs;
            $class = '\\' . $ns . '\Action\\' . $type . $modelName;
            if( class_exists( $class ) )
                return $class;
            
            $code = self::generate( $ns , $modelName , $type );
            eval( $code );
            return $class;
        }
	}

}

?>
