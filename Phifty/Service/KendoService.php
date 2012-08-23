<?php
namespace Phifty\Service;
use Exception;

class KendoService
    implements ServiceInterface
{
    public function getId() { return 'KendoAccessControl'; }

    public function register($kernel,$options = array())
    {
        $self = $this;
        $kernel->acl = function() use($self,$kernel) { };
    }
}


