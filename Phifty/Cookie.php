<?php

namespace Phifty;

class Cookie
{
	var $domain;
	var $expired;
	var $secure;
	var $path;


	function set($name,$value)
	{
		setcookie( $name, $value , $this->expired , $this->path , $this->domain, $this->secure );
	}

}



?>
