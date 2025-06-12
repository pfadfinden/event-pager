<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\TransportManager\Query\AllTransports;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;

#[AsCommand(name: 'app:transport:list', description: 'List transport configurations')]
final class ListTransportsCommand extends Command
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('enabled', mode: InputOption::VALUE_NEGATABLE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = AllTransports::withoutFilter();

        if (true === $input->getOption('enabled')) {
            $query = AllTransports::thatAreEnabled();
            $io->title('Enabled Transports');
        } elseif (false === $input->getOption('enabled')) {
            $query = AllTransports::thatAreDisabled();
            $io->title('Disabled Transports');
        } else {
            $io->title('All Transports');
        }

        $configs = iterator_to_array($this->queryBus->get($query));

        if (0 === count($configs)) {
            $io->info('No transports found');

            return Command::SUCCESS;
        }

        $io->table(
            ['Key', 'Enabled', 'Title', 'Transport'],
            array_map(fn ($c) => [$c->getKey(), $c->isEnabled() ? 'Yes' : 'No', $c->getTitle(), $c->getTransport()], $configs)
        );

        return Command::SUCCESS;
    }
}
