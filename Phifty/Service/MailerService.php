<?php
namespace Phifty\Service;
use Swift_MailTransport;
use Swift_Mailer;

class MailerService implements ServiceInterface
{

    public function register($kernel, $options = array() )
    {
        $kernel->mailer = function() use ($kernel) {
			$kernel->classloader->addPrefix(array(
				'Swift' => $kernel->frameworkDir . '/vendor/pear',
			));

            require $kernel->frameworkDir . '/vendor/pear/swift_required.php';

            // Mail transport
            $transport = Swift_MailTransport::newInstance();

            // Create the Mailer using your created Transport
            return Swift_Mailer::newInstance($transport);
        };

    }

}



