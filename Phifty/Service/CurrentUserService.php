<?php
namespace Phifty\Service;

class CurrentUserService
	implements ServiceInterface
{
	public function register($kernel,$options = array() )
	{
		// current user builder
        $kernel->currentUser = function() use ($kernel) {
            if( $currentUserClass = $kernel->config->get('framework','current_user.class') )
                return new $currentUserClass;
        };
	}
}





