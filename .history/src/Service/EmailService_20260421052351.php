<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {
    }

    /**
     * Send OTP code to email with HTML template
     */
    public function sendOtp(string $to, string $code): bool
    {
        try {
            $html = $this->twig->render('emails/otp_code.html.twig', [
                'otp_code' => $code,
                'email' => $to,
            ]);

            $email = (new Email())
                ->from('noreply@agrimans.com')
                ->to($to)
                ->subject('Code OTP Agrimans - Vérification')
                ->html($html);

            $this->mailer->send($email);

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'envoi du email: ' . $e->getMessage());
        }
    }

    /**
     * Send OTP code with plain text (fallback)
     */
    public function sendOtpPlainText(string $to, string $code): bool
    {
        try {
            $email = (new Email())
                ->from('noreply@agrimans.com')
                ->to($to)
                ->subject('Votre Code OTP Agrimans')
                ->text("Votre code OTP est : " . $code . "\n\nCe code expire dans 5 minutes.\n\nNe partagez pas ce code avec quelqu'un d'autre.");

            $this->mailer->send($email);

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'envoi du email: ' . $e->getMessage());
        }
    }
}