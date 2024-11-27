<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un utilisateur administrateur avec le rôle ROLE_ADMIN.',
)]
class CreateAdminCommand extends Command
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
        $output->writeln('Création de l\'administrateur...');

        // Création de l'utilisateur
        $admin = new User();
        $admin->setEmail('admin@example.com'); // Changez l'email selon vos besoins
        $admin->setRoles(['ROLE_ADMIN']);

        // Hachage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123'); // Changez le mot de passe
        $admin->setPassword($hashedPassword);

        // Enregistrement dans la base de données
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $output->writeln('Administrateur créé avec succès : admin@example.com (Mot de passe : admin123)');

        return Command::SUCCESS;
    }
}
