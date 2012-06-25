<?php
namespace Phifty\Command;
use CLIFramework\Command;

/**
 * When running asset:init command, we should simply register app/plugin assets 
 * into .assetkit file.
 *
 * Then, By running asset:update command, phifty will install assets into webroot.
 *
 *      phifty.php asset init
 *
 *      phifty.php asset update
 */
class AssetCommand extends Command
{
    function init()
    {
        $this->registerCommand('init', 'Phifty\Command\AssetInitCommand');
        $this->registerCommand('install', 'Phifty\Command\AssetInstallCommand');
    }
}



