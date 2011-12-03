<?php

namespace Phifty\View;

/* 
 * A Generic Action View Generator 
 *
 *    $action = Phifty\View\Action
 *
 * */
class Action 
{
    public $action;

    function __construct( $action ) 
    {
        $this->action = $action;
    }

    function render()
    {
        echo $this->action->getName();

    }
}

?>
