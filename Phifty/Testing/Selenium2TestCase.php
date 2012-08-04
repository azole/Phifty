<?php
namespace Phifty\Testing;
use PHPUnit_Extensions_Selenium2TestCase;


abstract class Selenium2TestCase extends PHPUnit_Extensions_Selenium2TestCase 
{

    protected function setUp()
    {
        $kernel = kernel();
        $kernel->config->load('testing','config/testing.yml');
        $config = $kernel->config->get('testing');
        if($config && $config->Selenium) {

            if($config->Selenium->Host)
                $this->setHost($config->Selenium->Host);

            if($config->Selenium->Port) 
                $this->setPort($config->Selenium->Port);

            if($config->Selenium->Browser)
                $this->setBrowser($config->Selenium->Browser);

            if($config->Selenium->BrowserUrl)
                $this->setBrowserUrl($config->Selenium->BrowserUrl);
        }
    }

}

