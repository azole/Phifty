<?php
namespace plugin;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    function brief() { return 'generate plugin structure'; }

    function generate($pluginName) 
    {
        $pluginDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
        $this->mkdir( $pluginDir );
        $this->mkdir( $pluginDir . DIRECTORY_SEPARATOR . 'Model' );
        $this->mkdir( $pluginDir . DIRECTORY_SEPARATOR . 'Controller' );
        $this->mkdir( $pluginDir . DIRECTORY_SEPARATOR . 'Action' );
        $this->mkdir( $pluginDir . DIRECTORY_SEPARATOR . 'template' );
        $this->mkdir( $pluginDir . DIRECTORY_SEPARATOR . 'assets' );
    }
}
