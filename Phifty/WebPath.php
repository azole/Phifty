<?php

namespace Phifty;

class WebPath
{
	static function appBase()
	{
		return '/ph/App';
	}

	static function coreBase()
	{
		return '/ph/Core';
	}

    static function pluginBase()
    {
        return '/ph/plugins';
    }

	static function widgetBase()
	{
		return '/ph/widgets';
	}

    static function minifiedBase()
    {
        return '/static/minified';
    }
}

