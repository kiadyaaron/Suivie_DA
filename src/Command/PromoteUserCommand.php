<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:promote-user',
    description: 'Attribue le rôle ROLE_ADMIN à un utilisateur existant',
)]
class PromoteUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l’utilisateur à promouvoir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln("<error> Utilisateur avec l’email « $email » introuvable.</error>");
            return Command::FAILURE;
        }

        $user->setRoles(['ROLE_ADMIN']);
        $this->entityManager->flush();

        $output->writeln("<info> $email a maintenant le rôle ROLE_ADMIN.</info>");
        return Command::SUCCESS;
    }
}