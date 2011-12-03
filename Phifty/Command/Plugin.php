<?php

namespace Phifty\Command;

use Phifty\FileUtils;
use Phifty\CodeTemplate;

class Plugin extends \Phifty\Command
{

    /*
     -M model
     -A action
     -C controller
     -V view

     -v verbose
    */
    public $longOpts = array( 
        "M|model::+", 
        "A|action::+",
        "C|controller::+", 
        "V|view::",
        "v|verbose",
        "f|force"
    );


    function putCode( $targetPath,  $templatePath , $args )
    {
        $codetpl = new CodeTemplate();
        if( $codetpl->hasTemplate( $templatePath ) ) {
            $text = $codetpl->render(  $templatePath , $args );
            if( $text )
                file_put_contents( $targetPath , $text );
        }
    }

    function run()
    {
        $options = $this->getOptions();
        $pluginName = $this->getArg(0 + 2);

        if( ! $pluginName )
            die( "Plugin name please" );

        // create a plugin 
        if( preg_match( '/\W/', $pluginName ) ) {
            die( "Invalid plugin name: a-z, A-Z, 0-9 please" );
        }

        $this->log( "Plugin name: $pluginName" );

        $pluginId = $pluginName;
        $pluginDir  = 'plugins' . DIRECTORY_SEPARATOR . $pluginId;

        $dirs = array();
        $dirs[] = $pluginDir . DIRECTORY_SEPARATOR .  'web';   # web static files
        $dirs[] = $pluginDir . DIRECTORY_SEPARATOR .  'template';  # sub-template
        $dirs[] = $pluginDir . DIRECTORY_SEPARATOR .  'View';  # view class
        $dirs[] = $pluginDir . DIRECTORY_SEPARATOR .  'Action';
        $dirs[] = $pluginDir . DIRECTORY_SEPARATOR .  'Controller';
        $dirs[] = $pluginDir . DIRECTORY_SEPARATOR .  'Model';

        echo "Initialize directories for plugin\n";
        FileUtils::mkpath( $dirs , true );

        foreach( $dirs as $dir )
            FileUtils::create_keepfile( $dir );

        // something like    plugins/SB/SB.php
        $pluginClassFile = FileUtils::path_join( $pluginDir , $pluginName . '.php' );

        if( ! file_exists( $pluginClassFile ) || @$options->force ) {
            $this->log( "Creating plugin class file: $pluginClassFile" );
            $this->putCode( $pluginClassFile , 'plugin/Plugin.php' , array( "PluginName" => $pluginName ) );
        }

        # echo "Options\n";
        # var_dump( $options );

        if( @$options->model ) {
            foreach( $options->model as $mName ) {
				$this->log( "Creating Model $mName ..." );

                $path = FileUtils::path_join( $pluginDir , 'Model' , $mName . '.php' );

                if( ! @$options->force ) {
                    if( file_exists( $path ) ) {
                        $this->log( "\tSkiping $path." );
                        continue;
                    }
                }

                $this->log( "Creating model file for $mName" );
                $this->log( "\t$path" );
                $this->putCode( $path , 'Model.php', array(
                    "ModelName" => $mName,
                    "Scope"     => $pluginName,
                ));
            }
        }

        if( @$options->action ) {
            foreach( $options->action as $aName ) {
				$this->log( "Creating Action $aName ..." );

                $path = FileUtils::path_join( $pluginDir , 'Action' , $aName . '.php' );
                if( ! @$options->force ) {
                    if( file_exists( $path ) ) {
                        $this->log( "\tSkipping $path." );
                        continue;
                    }
                }
                $this->log( "\t$path" );
                $this->putCode( $path , 'Action.php', array(
                    "Scope" => $pluginName,
                    "ActionName" => $aName,
                ));
            }
        }


        if( @$options->controller ) {
            foreach( $options->controller as $name ) {
				$this->log( "Creating Controller $name ..." );

                $path = FileUtils::path_join( $pluginDir , 'Controller' , $name . '.php' );
                if( ! @$options->force ) {
                    if( file_exists( $path ) ) {
                        $this->log( "\tFile $path exists. skip." );
                        continue;
                    }
                }

                $this->log( "\t$path" );
                $this->putCode( $path , 'Controller.php', array(
                    "Scope" => $pluginName,
                    "ControllerName" => $name,
                ));
            }
        }

    }
}

?>
