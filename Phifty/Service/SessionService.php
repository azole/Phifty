<?php
namespace Phifty\Service;

class SessionService 
    implements ServiceInterface
{

    function register($kernel)
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

