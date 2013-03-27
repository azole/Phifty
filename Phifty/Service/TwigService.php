<?php
namespace Phifty\Service;

class TwigService
    implements ServiceInterface
{
    public function getId() { return 'Twig'; }

    public function register($kernel, $options = array() )
    {
        $kernel->twig = function() {

        };
    }
}
