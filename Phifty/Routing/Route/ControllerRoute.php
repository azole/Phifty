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
		$method = $this->get('method'); // dispatch to method directly.
        $action = $this->get('action'); // controller action name
		$controller = new $controllerClass( $this );
        return $controller->runAction( $action );
    }
}

