<?php
namespace Phifty\Service;
use Swift_MailTransport;
use Swift_Mailer;
use Phifty\Config\Accessor;

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

        MailerService:
          Transport: SmtpTransport
          Username: your username
          Password: your password
          Host: smtp.example.org
          Port: 587
          SSL: true

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

            $accessor = Accessor( $options );
            $transportType = $accessor->transport ?: 'MailTransport';
            $transportClass = 'Swift_' . $transportType;
            $transport = null;

            switch( $transportType ) {

                case 'MailTransport':
                    $transport = $transportClass::newInstance();
                break;

                case 'SendmailTransport':
                    // sendmail transport has defined a built-in default command.
                    $command = $accessor->Command;
                    $transport = Swift_SendmailTransport::newInstance($command); 
                break;

                case 'SmtpTransport':
                    $host = $accessor->Host ?: 'localhost';
                    $port = $accessor->Port ?: 25;
                    $username = $accessor->Username;
                    $password = $accessor->Password;
                    $transport = Swift_SmtpTransport::newInstance($host, $port);
                    $transport->setUsername($username)
                    $transport->setPassword($password);
                break;

                default:
                    throw new Exception("Unsupported transport type: $transportType");
            }

            // Create the Mailer using your created Transport
            // return Swift_Mailer::newInstance($transport);
            return Swift_Mailer::newInstance($transport); // $mailer
        };

    }

}



