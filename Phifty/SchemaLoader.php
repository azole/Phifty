<?php
$finder = new LazyRecord\Schema\SchemaFinder;
foreach( kernel()->applications as $app ) {
    $finder->addPath( $app->locate() );
}

foreach( kernel()->plugins->getPlugins() as $plugin ) {
    $finder->addPath( $plugin->locate() );
}

$finder->loadFiles();
return $finder->getSchemas();
