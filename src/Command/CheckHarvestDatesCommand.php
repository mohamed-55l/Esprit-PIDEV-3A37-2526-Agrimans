<?php

namespace App\Command;

use App\Repository\CultureRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:check-harvest-dates',
    description: 'Vérifie les cultures dont la date de récolte est dans 7 jours et envoie un email.',
)]
class CheckHarvestDatesCommand extends Command
{
    private CultureRepository $cultureRepository;
    private MailerInterface $mailer;

    public function __construct(CultureRepository $cultureRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->cultureRepository = $cultureRepository;
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = 7; // On vérifie exactement 7 jours avant

        $cultures = $this->cultureRepository->findCulturesToHarvestInExactlyDays($days);

        if (empty($cultures)) {
            $io->success('Aucune culture à récolter dans ' . $days . ' jours.');
            return Command::SUCCESS;
        }

        $io->info('Trouvé ' . count($cultures) . ' culture(s) à récolter dans ' . $days . ' jours.');

        $sentCount = 0;
        foreach ($cultures as $culture) {
            $nomCulture = $culture->getNom();
            $nomParcelle = $culture->getParcelle() ? $culture->getParcelle()->getNom() : 'Inconnue';
            $dateStr = $culture->getDateRecoltePrevue()->format('d/m/Y');

            $email = (new Email())
                ->from('onboarding@resend.dev')
                ->to('medazizbelarbi@gmail.com')
                ->subject('Alerte Récolte Imminente : ' . $nomCulture)
                ->text("Bonjour,\n\nCeci est une notification automatique.\nLa récolte de la culture '$nomCulture' sur la parcelle '$nomParcelle' est prévue pour le $dateStr, soit dans exactement $days jours.\n\nPréparez-vous pour la récolte !\n\nL'équipe Agrimans.");

            try {
                $this->mailer->send($email);
                $sentCount++;
                $io->text("Email envoyé pour la culture : $nomCulture");
            } catch (\Exception $e) {
                $io->error("Erreur lors de l'envoi de l'email pour $nomCulture : " . $e->getMessage());
            }
        }

        $io->success("$sentCount email(s) envoyé(s) avec succès.");

        return Command::SUCCESS;
    }
}
