<?php
namespace Phifty;
use Phifty\Model\Column;
use LazyRecord\ModelBase;

class Model extends ModelBase 
{
	/* export schema to database ?  */
	public $export = true;

    function initBaseData()
    {

    }

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

    protected function column( $name ) 
    {
        return $this->columns[ $name ] = new \Phifty\Model\Column($name , $this );
    }

    private function _newAction($type)
    {
        $class = get_class($this);
        $actionClass = \Phifty\Action\RecordAction::createCRUDClass( $class, $type);
        $action = new $actionClass( array(), $this );
        return $action;
    }
}

?>
