<?php
namespace Phifty;
use Phifty\Model\Column;
use LazyRecord\BaseModel;
use ActionKit\RecordAction\BaseRecordAction;

class Model extends BaseModel 
{

    function getCurrentUser() 
    {
        return kernel()->currentUser;
    }

    function asCreateAction()
    {
        return $this->_newAction( 'Create' );
    }

    function asUpdateAction()
    {
        return $this->_newAction( 'Update' );
    }

    function asDeleteAction()
    {
        return $this->_newAction( 'Delete' );
    }

    /**
     * Create an action from existing record object
     *
     * @param string $type 'create','update','delete'
     *
     * TODO: Move to ActionKit
     */
    private function _newAction($type)
    {
        $class = get_class($this);
        $actionClass = BaseRecordAction::createCRUDClass($class,$type);
        return new $actionClass( array(), $this );
    }
}

