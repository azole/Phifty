<?php
namespace Phifty\Service;

class ConfigService
    implements ServiceInterface
{
    public function register($kernel, $options = array() )
    {
        $kernel->config = function() {  
            // build config loader

        };
    }
}






