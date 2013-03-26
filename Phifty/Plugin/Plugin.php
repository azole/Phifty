<?php
namespace Phifty\Plugin;
use Phifty\Bundle;
use Phifty\FileUtils;

class Plugin extends Bundle
{
    public $basePath;

    public function getName()
    {
        return $this->getNamespace();
    }
}
