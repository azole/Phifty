<?php
namespace Phifty\Service;

class CurrentUserService
    implements ServiceInterface
{

    public function getId() { return 'current_user'; } 

    public function register($kernel,$options = array() )
    {
        // current user builder
        $kernel->currentUser = function() use ($kernel,$options) {
            // framework.CurrentUser.Class is for backward compatible.
            $modelClass = isset($options['Model'])
                ? $options['Model']
                : $kernel->config->get('framework','CurrentUser.Model');
            $currentUserClass = isset($options['Class'])
                ? $options['Class']
                : $kernel->config->get('framework','CurrentUser.Class') ?: 'Phifty\Security\CurrentUser';
            return new $currentUserClass;
        };
    }
}





