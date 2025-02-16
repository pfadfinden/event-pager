<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\UserManagement\Command\AddUser;
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
        private readonly CommandBus $commandbus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, '(Unique) username of the User')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password of the User, if no password is provided, a random password will be generated')
            ->addOption('displayName', 'd', InputOption::VALUE_REQUIRED, 'The display name of the User, defaults to the username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Add a new User');

        $username = $input->getArgument('username');
        $password = $input->getOption('password');
        $displayName = $input->getOption('displayName');

        if (null === $displayName || '' === $displayName) {
            $displayName = $username;
        }

        $result_string = 'User %s added successfully.';

        if (null === $password || '' === $password) {
            $password = bin2hex(random_bytes(8));
            $result_string .= ' The password is: ';
            $result_string .= $password;
        }

        $this->commandbus->do(AddUser::with($username, $password, $displayName));

        $io->success(sprintf($result_string, $username));

        return Command::SUCCESS;
    }
}
