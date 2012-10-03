<?php
namespace Phifty;
use Phifty\Model\Column;
use LazyRecord\BaseModel;
use ActionKit\RecordAction\BaseRecordAction;

class Model extends BaseModel 
{
    function getLabel()
    {
        $label = parent::getLabel();
        return $label ? _($label) : $label;
    }

    function getCurrentUser() 
    {
        return kernel()->currentUser;
    }

    public function asCreateAction($args = array())
    {
        return $this->_newAction('Create',$args);
    }

    public function asUpdateAction($args = array())
    {
        return $this->_newAction('Update',$args);
    }

    public function asDeleteAction($args = array())
    {
        return $this->_newAction('Delete',$args);
    }

    /**
     * Create an action from existing record object
     *
     * @param string $type 'create','update','delete'
     *
     * TODO: Move to ActionKit
     */
    private function _newAction($type, $args = array() )
    {
        $class = get_class($this);
        $actionClass = BaseRecordAction::createCRUDClass($class,$type);
        return new $actionClass( $args , $this );
    }
}

