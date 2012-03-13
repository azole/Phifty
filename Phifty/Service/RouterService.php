<?php
namespace Phifty\Service;
use Roller\Router;

class RouterService 
    implements ServiceInterface
{

    function register($kernel) 
    {
        $kernel->router = function() {
            return new Router(null, array( 
                'route_class' => 'Phifty\Routing\Route',
                // 'cache_id' => PH_APP_NAME,
            ));
        };
    }

}



