<?php

namespace Phifty;


class ConfigChecker 
{
    var $config ;

    function __construct($config) 
    {
        $this->config = $config;
        $this->checkI18n();
    }

    function checkI18n()
    {
        $config = $this->config;

    }


}

?>
