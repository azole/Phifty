<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Phifty\FileUtils;
use Phifty;

class LocaleParseCommand extends Command
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
        $appPoFiles = array();
        $frameworkId = Kernel::FRAMEWORK_ID;
        $appId       = $kernel->config->framework->ApplicationID;

        $frameworkPoFilename = $frameworkId . '.po';
        $appPoFilename       = $appId . '.po';

        // prepare po files from framework po source files,
        // if we don't have one for the specific language.
        foreach( $langs as $langId ) {
            $poDir        = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES';
            $sourcePoPath = $frameworkLocaleDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $frameworkId . '.po';
            $targetPoPath = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $appId . '.po';

            if ( ! file_exists($poDir) ) {
                mkdir($poDir, 0755, true);
            }

            if ( $this->options->force || file_exists( $sourcePoPath ) && ! file_exists( $targetPoPath ) ) {
                $this->logger->info("Creating $targetPoPath");

                if ( $sourcePoPath != $targetPoPath ) {
                    copy($sourcePoPath, $targetPoPath);
                }
            }
        }

        // Compile templates from plugins
        $this->logger->info("Compiling templates...");
        $engine = new Phifty\View\Twig;
        $twig = $engine->getRenderer();
        foreach( $kernel->plugins as $plugin ) {
            $pluginDir = $plugin->locate();
            $templateDir = $plugin->getTemplateDir();
            if ( ! file_exists($templateDir) ) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($templateDir),
                    RecursiveIteratorIterator::LEAVES_ONLY) as $file) 
            {
                // force compilation
                if( preg_match( '/\.(html?|twig)$/', $file ) ) {
                    $this->logger->info( FileUtils::remove_cwd($file->getPathname()) ,1);
                    $twig->loadTemplate( substr($file, strlen(dirname($pluginDir)) + 1) );
                }
            }
        }

        $potFile = $localeDir . DIRECTORY_SEPARATOR . 'messages.pot';
        touch($potFile);

        $scanDirs = array();
        $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'src'; // phifty src
        $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'applications'; // phifty applications
        $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'bundles';
        $scanDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';
        $scanDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'bundles';
        $scanDirs[] = $kernel->getCacheDir();
        $scanDirs = array_filter( $scanDirs, 'file_exists' );

        $cmd = sprintf("xgettext -j -o %s --from-code=UTF-8 -n -L PHP -D $(find %s -type f -iname '*.php')",
            $potFile,
            join( ' ', $scanDirs ) );

        $this->logger->debug($cmd,1);
        system($cmd, $retval);
        if ( $retval != 0 )
            die('xgettext error');

        $this->logger->info("Updating message catalog...");

        // Update message catalog
        $finder = Finder::create()->files()->name('*.po')->in( $localeDir );
        foreach ( $finder->getIterator() as $file ) {
            $shortPathname = $file;

            $this->logger->info("Updating $shortPathname");
            $cmd = sprintf('msgmerge --update %s %s', $shortPathname, $potFile);
            $this->logger->debug($cmd,1);
            system($cmd, $retval);
            if ( $retval != 0 )
                die('xgettext error');

            $this->logger->info("Compiling messages $shortPathname");
            $cmd = sprintf('msgfmt -v %s', $shortPathname);
            $this->logger->debug($cmd);
            system($cmd, $retval);
            if ( $retval != 0 )
                die('xgettext error');
        }
    }

}
