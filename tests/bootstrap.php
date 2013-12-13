<?php
/**
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
require 'vendor/autoload.php';
require 'src/Phifty/Bootstrap.php';
// require 'selenium_helpers.php';
kernel()->classloader->addFallback( 'tests' );
kernel()->classloader->addFallback( dirname(__DIR__) . '/src' );
