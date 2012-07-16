<?php
namespace Phifty\Testing;
use PHPUnit_Extensions_Selenium2TestCase;

class SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase
{
    public $kernel;

    function setUp() {
        $this->kernel = kernel();
        $config = $this->kernel->config('testing');
        // $this->setBrowserUrl('http://www.example.com/');
    }

}


