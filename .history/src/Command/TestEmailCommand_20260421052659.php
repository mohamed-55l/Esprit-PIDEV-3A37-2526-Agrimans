<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test OTP email sending',
)]
class TestEmailCommand extends Command
{
    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email to send test OTP')
            ->addArgument('code', InputArgument::OPTIONAL, 'OTP code (default: 123456)', '123456');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $code = $input->getArgument('code');

        $io->title('🧪 Test Email OTP');
        $io->info("Envoi du code OTP à: $email");
        $io->info("Code: $code");

        try {
            $this->emailService->sendOtp($email, $code);
            $io->success('✅ Email OTP envoyé avec succès!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de l\'envoi: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
