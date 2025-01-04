<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\IntelPage\Application\IntelPageTransmitter;
use App\Core\IntelPage\Model\CapCode;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function sprintf;

#[AsCommand(name: 'app:intel-page:send-message', description: 'Send a test message to an IntelPage transmitter')]
final class IntelPageSendTestMessageCommand extends Command
{
    public function __construct(
        #[Autowire(param: 'intel_page.transmitter.host')]
        private readonly string $defaultTransmitterHost,
        #[Autowire(param: 'intel_page.transmitter.port')]
        private readonly int $defaultTransmitterPort,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to send a test message to an IntelPage transmitter. <br/>You must provide a valid CAP code along with the message body. Optionally, a transmitter endpoint can be defined, otherwise the environment configuration will be used.')
            ->addArgument('cap', InputArgument::REQUIRED, '')
            ->addArgument('message', InputArgument::REQUIRED, 'the message body')
            ->addOption('transmitterUrl', 't', InputOption::VALUE_REQUIRED, '', $this->defaultTransmitterHost)
            ->addOption('transmitterPort', 'p', InputOption::VALUE_REQUIRED, '', $this->defaultTransmitterPort)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('IntelPage: Test Message Utility');

        $capCode = (int) $input->getArgument('cap');
        $message = $input->getArgument('message');

        if ($capCode <= 0 || $capCode > 9999) {
            throw new InvalidArgumentException('The entered channel '.$capCode.' is not a valid integer.');
        }

        if ($output->isVeryVerbose()) {
            $io->comment(sprintf("CAP Code: %d\nMessage: %s", $capCode, $message));
        }

        $transmitterUrl = $input->getOption('transmitterUrl');
        $transmitterPort = (int) $input->getOption('transmitterPort');

        if ($output->isVerbose()) {
            $io->note(sprintf('Using transmitter at %s:%d', $transmitterUrl, $transmitterPort));
        }

        // ##############################

        $transmitter = new IntelPageTransmitter($transmitterUrl, $transmitterPort);
        try {
            $transmitter->transmit(CapCode::fromInt($capCode), $message);
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Successfully send message to cap code %d', $capCode));

        return Command::SUCCESS;
    }
}
