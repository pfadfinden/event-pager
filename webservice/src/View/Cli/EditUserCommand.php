<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\UserManagement\Command\EditUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand(name: 'app:user:edit', description: 'Edit a User')]
final class EditUserCommand extends Command
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
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'New Password for the User')
            ->addOption('displayname', 'd', InputOption::VALUE_REQUIRED, 'New Display Name for the User')
            ->addOption('addRole', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Add Role(s) to the User')
            ->addOption('removeRole', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Revoke Role(s) from the User')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Edit a User');

        $this->commandbus->do(EditUser::with(
            $input->getArgument('username'),
            $input->getOption('password'),
            $input->getOption('displayname'),
            $input->getOption('addRole'),
            $input->getOption('removeRole'),
        ));

        $io->success(sprintf('User %s edited successfully.', $input->getArgument('username')));

        return Command::SUCCESS;
    }
}
