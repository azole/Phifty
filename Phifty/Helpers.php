<?php

// a global shorter helper function to get AppKernel instance.
function webapp() 
{
    return AppKernel::getInstance();
}

function web_debug( $msg )
{
    echo "<div>$msg</div>\n";
}


/* export webapp to global variable */
global $webapp;
$webapp = AppKernel::getInstance();
