<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailerController extends AbstractController
{
    #[Route('/send-email', name: 'app_send_email')]
    public function index(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('sample-sender@binaryboxtuts.com')
            ->to('sample-recipient@binaryboxtuts.com')
            ->subject('Email Test')
            ->text('A sample em ail using mailtrap.')
            ->html('<html>
    <body>
        <p>Hello<br>
            Verify email.
        </p>
    </body>
</html>
');

        $mailer->send($email);
        return new Response(
            'Email sent successfully'
        );
    }
}
