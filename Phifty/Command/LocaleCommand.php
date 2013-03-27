<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;

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

    public function options($opts)
    {
        $opts->add('f|force','force');
    }


    public function execute()
    {
        $kernel = kernel();
        $localeDir = $kernel->config->get('framework','Services.LocaleService.Directory') ?: 'locale';
        $frameworkLocaleDir = PH_ROOT . DIRECTORY_SEPARATOR . 'locale';
        $langs     = $kernel->config->get('framework','Services.LocaleService.Langs')->config;

        $cwd = getcwd();
        $finder = Finder::create()->files()->name('*.po')->in( $localeDir );
        $appPoFiles = array();
        $frameworkId = Kernel::FRAMEWORK_ID;
        $appId       = $kernel->config->framework->ApplicationID;

        $frameworkPoFilename = $frameworkId . '.po';
        $appPoFilename       = $appId . '.po';

        foreach( $langs as $langId ) {
            $poDir        = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES';
            $sourcePoPath = $frameworkLocaleDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $frameworkId . '.po';
            $targetPoPath = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $appId . '.po';

            if ( ! file_exists($poDir) ) {
                mkdir($poDir, 0755, true);
            }

            if ( $this->options->force || file_exists( $sourcePoPath ) && ! file_exists( $targetPoPath ) ) {
                $this->logger->info("Creating $targetPoPath");
                copy($sourcePoPath, $targetPoPath);
            }
        }

        /*
        foreach ( $finder->getIterator() as $file ) {
            $shortPath     = substr( $file->getPath() , strlen($cwd) + 1 );
            $shortPathname = substr( $file->getPathname() , strlen($cwd) + 1 );
            $targetPoPathname = $shortPath . DIRECTORY_SEPARATOR . $appPoFilename;
            $this->logger->info("Found $shortPathname",1);
        }
         */
    }

}
