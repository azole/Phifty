<?php
namespace Phifty\Service;

class TwigService
	implements ServiceInterface
{
    public function getId() { return 'Twig'; }

	public function register($kernel, $options = array() )
	{
		$kernel->classloader->addPrefix(array(
			'Twig_Extensions_'   => $kernel->frameworkDir . '/vendor/twig-extensions/lib',
			'Twig_'              => $kernel->frameworkDir . '/vendor/pear',
		));
	}
}







