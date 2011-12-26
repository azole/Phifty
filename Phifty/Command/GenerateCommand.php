<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\CodeTemplate;

// xxx:

class GenerateCommand extends \CLIFramework\Command
{
    public $longOpts = array( 
        "C|core",
        "f|force",
    );

    function brief()
    {
        return 'generate files';
    }

    function run()
    {
        $type = strtolower($this->getArg(0 + 2));
        if( ! $type )
            $this->help();

        $name = $this->getArg(1 + 2);
        if( ! $name )
            $this->help();

        $options = $this->getOptions();

        $cwd = getcwd();
        $inCore = @$options->core;
        $codeGen = new CodeTemplate;

        $kernel = \AppKernel::one();
        $appdir = $inCore ? $kernel->getCoreDir() : $kernel->getAppDir();


        if( $inCore ) {
            $this->log( "In Phifty Core, generate for core/" );
        }

        # var_dump( $appdir );

        $this->log( "Generating $type: $name" );

        FileUtils::mkpath( FileUtils::path_join( $appdir, ucfirst($type) ) );

        $className = $name . ucfirst($type);
        $path = FileUtils::path_join( $appdir , ucfirst($type) , $className . '.php' );
        $this->log("\t$path");

        switch( $type ) 
        {

        case "controller":
            $codeGen->renderFile( $path , 'Controller.php' , array( 
                'Scope' => ($inCore ? 'Core' : $kernel->getAppName() ) ,
                'ControllerName' => $className ) , $options->force );
            break;

        case "action":
            $codeGen->renderFile( $path , 'Action.php' , array(
                'Scope' => ($inCore ? "Core" : $kernel->getAppName() ) ,
                'ActionName' => $className
                ) , $options->force );
            break;

        case "model":
            $codeGen->renderFile( $path , 'Model.php' , array(
                'Scope' => ($inCore ? "Core" : $kernel->getAppName() ) ,
                'ModelName' => $className
                ) , $options->force );
            break;
        case "view":
            $codeGen->renderFile( $path , 'View.php' , array(
                'Scope' => ($inCore ? "Core" : $kernel->getAppName() ) ,
                'ViewName' => $className
                ) , $options->force );
            break;
        }
        $this->log( "Done." );
    }
}

