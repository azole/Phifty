<?php
namespace Phifty\Service;
use Exception;

class SoapClientService
    implements ServiceInterface
{
    public function getId() { return 'SoapClient'; }

    public function register($kernel, $options = array() )
    {
        if ( ! isset($options["WSDL"]) ) {
            throw new Exception("WSDL is not defined.");
        }
        $kernel->soapClient = function() use ($options) {
            return new SoapClient( $options["WSDL"] );
        };
    }
}

