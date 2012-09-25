<?php
namespace Phifty\Service;
use Roller\Router;

class RouterService 
    implements ServiceInterface
{
    public function getId() { return 'Router'; }

    public function register($kernel, $options = array() ) 
    {
        $kernel->router = function() use ($kernel) {
            if( 'production' === $kernel->environment ) {
                return new Router(null, array( 
                    'route_class' => 'Phifty\Routing\Route',
                    'cache_id' => PH_APP_ROOT ? PH_APP_ROOT : $kernel->config->get('framework','uuid'),
                ));
            }
            return new Router(null, array( 
                'route_class' => 'Phifty\Routing\Route',
            ));
        };
    }
}

