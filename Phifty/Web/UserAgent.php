<?php

namespace Phifty\Web;

class UserAgent 
{

    public $agent;

    public function __construct() 
    {
        $this->agent = $_SERVER['HTTP_USER_AGENT'];
    }

    public function getAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function isiPhone() 
    {
        return preg_match( '/iPhone/' , $this->agent );
    }

    public function isiPad() 
    {
        return preg_match( '/iPad/' , $this->agent );
    }

    public function isMobile() 
    {
        return preg_match( '/iPhone|iPad|Android/' , $this->agent );
    }
}

