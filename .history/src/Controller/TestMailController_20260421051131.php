<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestMailController extends AbstractController
{
    #[Route('/test-mail', name: 'test_mail')]
    public function sendMail(MailerInterface $mailer): Response
    {
        try {
            $email = (new Email())
                ->from('zidisamir993@gmail.com')
                ->to('zidisamir991@gmail.com') // envoie à toi-même pour test
                ->subject('Test Symfony Mailer')
                ->text('Ceci est un test d\'envoi email depuis Symfony');

            $mailer->send($email);

            return new Response("✅ EMAIL ENVOYÉ AVEC SUCCÈS");

        } catch (\Exception $e) {
            return new Response("❌ ERREUR : " . $e->getMessage());
        }
    }
}