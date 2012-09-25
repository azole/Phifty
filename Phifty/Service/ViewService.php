<?php
namespace Phifty\Service;

/**
 * Usage:
 *
 *    $view = kernel()->view;
 */

class ViewService
    implements ServiceInterface
{
    public function getId() { return 'View'; }
    public function register($kernel, $options = array() ) 
    {

    }
}

