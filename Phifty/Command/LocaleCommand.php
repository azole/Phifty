<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Symfony\Component\Finder\Finder;

/**
 *
 * 1. create dictionary from locale files (po files)
 * 2. scan PHP files and look for _( ) and __( ) pattern
 * 3. build & scan twig templates
 * 4. rewrite po files
 *
 */
class LocaleCommand extends Command
{
    public function execute()
    {
        $kernel = kernel();
        $localeDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . $kernel->config->get('framework','Services.LocaleService.LocaleDir');
        $finder = Finder::create()->files()->name('*.po')->in( $localeDir );
        foreach ( $finder->getIterator() as $file ) {
            echo $file, "\n";
        }
    }

}
