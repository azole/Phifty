<?php
namespace Phifty\Command;
use CLIFramework\Command;

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

#          $finder = Finder::create()->files()->name('*.po')->in(
#              PH_ROOT . DIRECTORY_SEPARATOR . $kernel->config->get('framework','Locale.localedir')
#          );
#          foreach ( $finder->getIterator() as $file ) {
#              echo $file, "\n";
#          }
    }

}
