<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\TransportManager\Command\RemoveTransportConfiguration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand(name: 'app:transport:remove', description: 'Remove a transport configuration')]
final class RemoveTransportCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('key', InputArgument::REQUIRED, 'identifier for transport (slug)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Remove transport');

        $this->commandBus->do(new RemoveTransportConfiguration(
            $input->getArgument('key'),
        ));

        $io->success(sprintf('Transport configuration for "%s" was removed if it existed.', $input->getArgument('key')));

        return Command::SUCCESS;
    }
}
