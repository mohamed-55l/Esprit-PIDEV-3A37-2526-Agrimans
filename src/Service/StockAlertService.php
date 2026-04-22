<?php

namespace App\Service;

use App\Repository\EquipementRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class StockAlertService
{
    private EquipementRepository $equipementRepository;
    private MailerInterface $mailer;

    // Seuil de stock critique
    private const ALERT_THRESHOLD = 3; 

    // Adresse email de l'admin (pour le projet)
    private const ADMIN_EMAIL = 'admin@agrimans.com'; 

    public function __construct(EquipementRepository $equipementRepository, MailerInterface $mailer)
    {
        $this->equipementRepository = $equipementRepository;
        $this->mailer = $mailer;
    }

    /**
     * Vérifie le stock d'équipements disponibles et envoie un email si le stock est critique.
     */
    public function checkAndSendAlert(): void
    {
        // On récupère tous les équipements disponibles
        $disponiblesCount = 0;
        $equipements = $this->equipementRepository->findAll();
        
        foreach ($equipements as $equipement) {
            // Ajustez "Disponible" selon la casse exacte utilisée dans votre base
            if (strtolower(trim($equipement->getDisponibilite() ?? '')) === 'disponible') {
                $disponiblesCount++;
            }
        }

        // Si le nombre d'équipements disponibles est sous le seuil
        if ($disponiblesCount <= self::ALERT_THRESHOLD) {
            $this->sendAlertEmail($disponiblesCount);
        }
    }

    private function sendAlertEmail(int $disponiblesCount): void
    {
        $email = (new Email())
            ->from('zidisamir993@gmail.com')
            ->to(self::ADMIN_EMAIL)
            ->subject('⚠️ Alerte Stock Équipement : Stock Critique !')
            ->html(
                "<h1>Alerte Stock Équipement</h1>" .
                "<p>Bonjour Administrateur,</p>" .
                "<p>Ceci est une alerte automatisée du système AGRIMANS.</p>" .
                "<p>Le nombre d'équipements actuellement <strong>Disponibles</strong> est tombé à : <strong style='color: red; font-size: 1.2rem;'>" . $disponiblesCount . "</strong>.</p>" .
                "<p>Veuillez vérifier les stocks et prévoir de nouveaux achats ou réparations si nécessaire.</p>" .
                "<br><hr>" .
                "<p><em>Système de Gestion Agrimans</em></p>"
            );

        $this->mailer->send($email);
    }
}
