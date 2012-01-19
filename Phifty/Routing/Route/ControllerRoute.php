<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\Routing\Route;

use Exception;
use ReflectionObject;
use ReflectionFunction;
use Phifty\Routing\Route;
use Phifty\Routing\RouteInterface;

class ControllerRoute extends Route
{

    function evaluate()
    {
        $controllerClass  = $this->get('controller');
        $action = $this->get('action'); // controller action name
        $controller = new $controllerClass();
        return $controller->runAction( $action , array(
            'vars' => $this->getVars(),
            'default' => $this->getDefault(),
            'requirement' => $this->getRequirement(),
        ) );
    }

}

