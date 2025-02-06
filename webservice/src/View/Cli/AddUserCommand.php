<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\CommandBus; 
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
    }
}