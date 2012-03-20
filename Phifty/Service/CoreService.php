<?php
namespace Phifty\Service;

class CoreService 
    implements ServiceInterface
{
    public function getId() { return 'Core'; }

    public function register($kernel, $options = array() )
    {

    }
}
