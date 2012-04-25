<?php
namespace Phifty\Service;

class SessionService 
    implements ServiceInterface
{

    public function register($kernel, $options = array())
    {
        $kernel->session = function() {
            $session = new \Universal\Session\Session(array(  
                'state'   => new \Universal\Session\State\NativeState,
                'storage' => new \Universal\Session\Storage\NativeStorage,
            ));
            return $session;
        };
    }
}

