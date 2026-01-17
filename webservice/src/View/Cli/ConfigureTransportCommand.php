<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\TransportManager\Command\AddOrUpdateTransportConfiguration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand(name: 'app:transport:configure', description: 'Add or update a transport configuration')]
final class ConfigureTransportCommand extends Command
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
            ->addArgument('transport', InputArgument::REQUIRED, 'FQCN of Transport implementation')
            ->addArgument('title', InputArgument::REQUIRED, 'human label for transport')
            ->addArgument('vendorSpecificJson', InputArgument::OPTIONAL, 'identifier for transport (slug)')
            ->addOption('enable', mode: InputOption::VALUE_NEGATABLE, default: false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Add or Update Transport');

        /** @var array|null $vendorSpecificConfiguration @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array) */
        $vendorSpecificConfiguration = null !== $input->getArgument('vendorSpecificJson') ? json_decode((string) $input->getArgument('vendorSpecificJson'), true) : null;
        $this->commandBus->do(AddOrUpdateTransportConfiguration::with(
            $input->getArgument('key'),
            $input->getArgument('transport'),
            $input->getArgument('title'),
            $input->getOption('enable'),
            $vendorSpecificConfiguration
        ));

        $io->success(sprintf('Transport configuration for "%s" updated.', $input->getArgument('key')));

        return Command::SUCCESS;
    }
}
