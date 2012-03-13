<?php

namespace Phifty\Service;

class SwiftMailerService implements ServiceInterface
{


    public function register($kernel)
    {
        $kernel->mailer = function() {

            require_once PH_ROOT . '/vendor/pear/swift_required.php';

            // Mail transport
            $transport = Swift_MailTransport::newInstance();

            // Create the Mailer using your created Transport
            return Swift_Mailer::newInstance($transport);
        };

    }

}



