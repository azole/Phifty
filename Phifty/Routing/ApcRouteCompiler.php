<?php
/*
 * This file is part of the Onion package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Phifty\Routing;

class ApcRouteCompiler 
        extends RouteCompiler
        implements RouteCompilerInterface
{

    public function compile(Array $route )
    {
        $pattern = $route['pattern'];
        if( ($compiled = webapp()->apc->get( $pattern ) ) !== null ) {
            return $compiled;
        }
        $compiled = parent::compile( $route );
        webapp()->apc->set( $pattern , $compiled );
        return $compiled;
    }

}

