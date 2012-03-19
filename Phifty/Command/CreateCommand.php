<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\Utils;
use Phifty\FileUtils;

class CreateCommand extends Command
{
    public function brief() { 
        return 'create new phifty app'; 
    }

    public function usage() { 
        return 'phifty create [app]'; 
    }

    public function options($opts)
    {
        $opts->add('b|branch:','branch name');
    }

    public function execute($appname)
    {
        $options = $this->getOptions();
        $logger = $this->logger;

        $branch = $options->branch ? $options->branch : 'develop';

        FileUtils::mkdir( $appname );
        chdir( $appname );
        $appdir = getcwd();

        Utils::system('git init');
        Utils::system("git submodule --quiet add -b $branch git@git.corneltek.com:phifty.git phifty");

        chdir('phifty');
        Utils::system('onion bundle');
        Utils::system('git submodule init');
        Utils::system('git submodule --quiet update');
        chdir($appdir);

        
        $dirs = array();
        $dirs[] = 'webroot';
        $dirs[] = 'webroot/static/upload';
        $dirs[] = 'cache';
        $dirs[] = 'applications';
        $dirs[] = 'plugins';
        foreach( $dirs as $dir )
            FileUtils::mkdir($dir);

        system('chmod -R og+rw cache');

        # cp -r phifty/config .
        Utils::rcopy('phifty/config','config');
        copy('phifty/webroot/.htaccess','webroot/.htaccess');
        copy('phifty/webroot/index.php','webroot/index.php');

        Utils::rcopy('phifty/locale','locale');


        $stub =<<<EOS
<?php
define('PH_APP_ROOT',__DIR__);
require __DIR__ . '/phifty/main.php'
?>
EOS;
        file_put_contents('main.php',$stub);

        echo <<<EOS
Done!

Please edit config file: 

    config/framework.yml
    config/database.yml

And run:

    phifty build-conf

EOS;


    }
}





