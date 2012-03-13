<?php
namespace Phifty\Service;
use Swift_MailTransport;
use Swift_Mailer;

class MailerService implements ServiceInterface
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



