<?php
$finder = new LazyRecord\Schema\SchemaFinder;
foreach( kernel()->applications as $app ) {
	$finder->addPath( $app->locate() );
}

foreach( kernel()->pluginManager->getPlugins() as $plugin ) {
	$finder->addPath( $plugin->locate() );
}

$finder->loadFiles();
return $finder->getSchemaClasses();
