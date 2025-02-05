<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\UserManagement\Command\AddUser;
use App\Core\Contracts\Bus\CommandBus; 
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand(name: 'app:user:add', description: 'Add a new User')]
final class AddUserCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandbus
    ) {
        parent::__construct();
    }

    protected function configure(): void 
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, '(Unique) username of the User')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password of the User, if no password is provided, a random password will be generated')
            ->addArgument('DisplayName', InputArgument::OPTIONAL, 'The display name of the User, defaults to the username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Add a new User');
    
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $displayName = $input->getArgument('DisplayName');

        if ($displayName == '') {
            $displayName = $username;
        }

        if ($password == '') {
            $password = bin2hex(random_bytes(8));
        }

        $this->commandbus->do(AddUser::with($username, $password, $displayName));

        $io->success(sprintf('User %s added successfully. Password is: %s', $username, $password));

        return Command::SUCCESS;
    }
}