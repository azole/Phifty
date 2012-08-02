<?php
namespace Phifty\Testing;
use PHPUnit_Extensions_Selenium2TestCase;


abstract class Selenium2TestCase extends PHPUnit_Extensions_Selenium2TestCase 
{

    protected $urlOf = [
        'login' => 'http://phifty.dev/bs/login',
        'news' => 'http://phifty.dev/bs/news'
    ];

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

    protected function login( $transferTo='' ) 
    {
        $this->url( $this->urlOf['login'] );

        $accountInput = get('input[name=account]');
        $accountInput->value('admin');

        $passwordInput = get('input[name=password]');
        $passwordInput->value('admin');

        get('.submit')->click();

        if ( '' !== $transferTo  )
            $this->url( $this->urlOf[ $transferTo ] );

        wait();
    }
}

