<?php
namespace Phifty\Service;
use Swift_MailTransport;
use Swift_Mailer;

class MailerService implements ServiceInterface
{

  /**

    $kernel->mailer->send( $message );

    Transport Configurations:

    @see http://swiftmailer.org/docs/sending.html

    SMTP Transport:

        $transport = Swift_SmtpTransport::newInstance('smtp.example.org', 25)
            ->setUsername('username')
            ->setPassword('password');

        MailerService:
          Transport: SmtpTransport
          Username: your username
          Password: your password
          Host: smtp.example.org
          Port: 25

    Sendmail Transport:

        $transport = Swift_SendmailTransport::newInstance('/usr/sbin/exim -bs');

        MailerService:
          Transport: SendmailTransport
          Command: '/usr/sbin/exim -bs'

    Mail Transport:

        $transport = Swift_MailTransport::newInstance();

        MailerService:
          Transport: MailTransport

    */
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
            // return Swift_Mailer::newInstance($transport);
            $mailer = Swift_Mailer::newInstance($transport);
            return $mailer;
        };

    }

}



