<?php

namespace App\Command;

use App\Entity\Users;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Create default admin and user',
)]
class CreateUsersCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Update or create Admin
        $admin = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => 'admin@agrimans.com']);
        if (!$admin) {
            $admin = new Users();
            $admin->setEmail('admin@agrimans.com');
            $admin->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($admin);
            $output->writeln('Admin created.');
        } else {
            $output->writeln('Admin updated.');
        }
        $admin->setFullName('Admin Agrimans');
        $admin->setRole(UserRole::ADMIN);
        $admin->setPasswordHash($this->passwordHasher->hashPassword($admin, '123'));

        // Update or create User
        $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => 'user@agrimans.com']);
        if (!$user) {
            $user = new Users();
            $user->setEmail('user@agrimans.com');
            $user->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);
            $output->writeln('User created.');
        } else {
            $output->writeln('User updated.');
        }
        $user->setFullName('User Agrimans');
        $user->setRole(UserRole::USER);
        $user->setPasswordHash($this->passwordHasher->hashPassword($user, '123'));

        $this->entityManager->flush();

        $output->writeln('Done!');

        return Command::SUCCESS;
    }
}
