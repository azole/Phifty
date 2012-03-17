<?php
namespace Phifty\Command;
use CLIFramework\Command;
use SplFileInfo;
use SerializerKit\Serializer;

class BuildConfCommand extends Command
{

    public function brief()
    {
        return 'build PHP configuration file from YAML.';
    }

    public function usage()
    {
        return 'build-conf [yaml filepath]';
    }

    public function execute($configPath)
    {
        if( ! class_exists('sfYaml',true) ) {
            require 'SymfonyComponents/YAML/sfYaml.php';
        }

        if( ! file_exists($configPath) ) {
            throw new Exception("$configPath file does not exist.");
        }

        $fileInfo = new SplFileInfo( $configPath );
        $ext = $fileInfo->getExtension();
        $configHash = null;
        switch( $ext ) {
            case "yml":
            case "yaml":
                $yaml = new Serializer('yaml');
                $configHash = $yaml->decode( file_get_contents( $fileInfo ) );
                break;
            default:
                throw new Exception("Can not convert config file.");
                break;
        }

        $php = new Serializer('php');
        $phpContent = '<?php ' . $php->encode($configHash) . ' ?>';

        // write config file
        $target = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getBaseName('.' . $ext) . '.php';
        $this->logger->info("Writing config file $target");
        file_put_contents( $target, $phpContent );

        $this->logger->info('Done');
    }
}


