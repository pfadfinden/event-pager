<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\UserManagement\Command\DeleteUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand(name: 'app:user:delete', description: 'Delete a User')]
final class DeleteUserCommand extends Command
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Delete a User');

        $username = $input->getArgument('username');

        $this->commandbus->do(DeleteUser::with($username));

        $io->success(sprintf('User %s deleted successfully.', $username));

        return Command::SUCCESS;
    }
}
