<?php
namespace Phifty\Service;
use Phifty\L10N;

class LocaleService
    implements ServiceInterface
{
    public function register($kernel)
    {
        $kernel->locale = function() {
            return new L10N;
        };
    }
}
