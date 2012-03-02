<?php
namespace Phifty;
use Phifty\Model\Column;
use Lazy\BaseModel;

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

    private function _newAction($type)
    {
        $class = get_class($this);
        $actionClass = \Phifty\Action\RecordAction::createCRUDClass( $class, $type);
        $action = new $actionClass( array(), $this );
        return $action;
    }
}

