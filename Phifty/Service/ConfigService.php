<?php
namespace Phifty\Service;
use Phifty\Config\ConfigManager;

class ConfigService
    implements ServiceInterface
{

    public $manager;

    public function __construct()
    {
        $this->manager = new ConfigManager;
    }

    public function register($kernel, $options = array() )
    {
        $self = $this;

        // setup environment
        $this->manager->environment = $kernel->environment;
        $kernel->config = function() use ($self) {  
            return $self->manager;
        };
    }

    public function load($section,$file) {
        return $this->manager->load($section,$file);
    }
}


