<?php
namespace bundle;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate bundle structure'; }

    public function generate($bundleName)
    {
        $bundleDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . $bundleName;
        $this->createDir( $bundleDir );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Model' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Controller' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Action' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'template' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'assets' );

        $classFile = $bundleDir . DIRECTORY_SEPARATOR . $bundleName . '.php';
        $this->render('Plugin.php.twig', $classFile, array(
            'bundleName' => $bundleName,
        ));
    }
}
