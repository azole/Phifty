<?php
namespace Phifty;
use Phifty\Model\Column;
use LazyRecord\BaseModel;
use ActionKit\RecordAction\BaseRecordAction;

class Model extends BaseModel 
{

    public function asCreateAction()
    {
        return $this->_newAction( 'Create' );
    }

    public function asUpdateAction()
    {
        return $this->_newAction( 'Update' );
    }

    public function asDeleteAction()
    {
        return $this->_newAction( 'Delete' );
    }

    // XXX: Move to ActionKit
    private function _newAction($type)
    {
        $class = get_class($this);
        $actionClass = BaseRecordAction::createCRUDClass( $class, $type);
        $action = new $actionClass( array(), $this );
        return $action;
    }
}

