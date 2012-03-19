<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\FileUtils;
use Symfony\Component\Finder\Finder;

class LocaleCommand extends Command
{

	public function execute()
	{
		$kernel = kernel();
		$finder = Finder::create()->files()->name('*.po')->in( 
			PH_ROOT . DIRECTORY_SEPARATOR . $kernel->config->get('framework','Locale.localedir')
		);

		foreach( $finder->getIterator() as $file ) {
			echo $file, "\n";
		}
	}

}

