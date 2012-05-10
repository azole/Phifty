<?php
namespace Phifty\Service;

class CurrentUserService
	implements ServiceInterface
{

    public function getId() { return 'CurrentUser'; } 

	public function register($kernel,$options = array() )
	{
		// current user builder
        $kernel->currentUser = function() use ($kernel) {
            if( $currentUserClass = $kernel->config->get('framework','CurrentUser.Class') )
                return new $currentUserClass;
        };
	}
}





