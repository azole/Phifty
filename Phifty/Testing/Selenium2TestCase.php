<?php
namespace Phifty\Testing;
use PHPUnit_Extensions_Selenium2TestCase;
use Exception;


abstract class Selenium2TestCase extends PHPUnit_Extensions_Selenium2TestCase 
{
    public $Environment;
    
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

            if($config->Environment)
                $this->Environment = $config->Environment;
        }

        // XXX: SeleniumTestCase (1.0) seems don't support screenshotPath ?
        $this->screenshotPath = $this->getScreenshotDir();
    }

    // Override the original method and tell Selenium to take screen shot when test fails
    public function onNotSuccessfulTest(\Exception $e) 
    {
        $this->takeScreenShot();
        parent::onNotSuccessfulTest($e);
    }

    public function getScreenshotDir() {
        return PH_ROOT . '/tests/screenshots'; 
    }

    public function takeScreenShot() 
    {
        $screenshotPath = $this->getScreenshotDir();

        $screenShot = $this->currentScreenshot();

        if ( !is_string( $screenShot ) || ! $screenShot ) {
            throw new Exception('Take ScreenShot failed');
        }
        
        $filePath1 = $screenshotPath . DIRECTORY_SEPARATOR . md5(rand()) . '.png'; // Create an unique file name
        $filePath2 = $screenshotPath . DIRECTORY_SEPARATOR . 'now.png'; // Create the easily recognized file name

        file_put_contents( $filePath1, $screenShot );
        file_put_contents( $filePath2, $screenShot );
    }
}

