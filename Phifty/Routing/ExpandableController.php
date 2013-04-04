<?php
namespace Phifty\Routing;
interface ExpandableController
{
    public static function expand();
    public static function set_mount_path($path);
}
