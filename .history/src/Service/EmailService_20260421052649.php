<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Send OTP code to email with HTML template
     */
    public function sendOtp(string $to, string $code): bool
    {
        try {
            $this->logger->info("Tentative d'envoi OTP à: " . $to);
            
            $html = $this->twig->render('emails/otp_code.html.twig', [
                'otp_code' => $code,
                'email' => $to,
            ]);

            $email = (new Email())
                ->from('zidisamir993@gmail.com')  // Doit correspondre au compte Gmail
                ->to($to)
                ->subject('Code OTP Agrimans - Vérification')
                ->html($html);

            $this->mailer->send($email);
            $this->logger->info("Email OTP envoyé avec succès à: " . $to);

            return true;
        } catch (\Exception $e) {
            $errorMsg = 'Erreur lors de l\'envoi du email: ' . $e->getMessage();
            $this->logger->error($errorMsg);
            throw new \RuntimeException($errorMsg);
        }
    }

    /**
     * Send OTP code with plain text (fallback)
     */
    public function sendOtpPlainText(string $to, string $code): bool
    {
        try {
            $this->logger->info("Tentative d'envoi OTP (texte) à: " . $to);
            
            $email = (new Email())
                ->from('zidisamir993@gmail.com')
                ->to($to)
                ->subject('Votre Code OTP Agrimans')
                ->text("Votre code OTP est : " . $code . "\n\nCe code expire dans 5 minutes.\n\nNe partagez pas ce code avec quelqu'un d'autre.");

            $this->mailer->send($email);
            $this->logger->info("Email OTP (texte) envoyé avec succès à: " . $to);

            return true;
        } catch (\Exception $e) {
            $errorMsg = 'Erreur lors de l\'envoi du email: ' . $e->getMessage();
            $this->logger->error($errorMsg);
            throw new \RuntimeException($errorMsg);
        }
    }
}