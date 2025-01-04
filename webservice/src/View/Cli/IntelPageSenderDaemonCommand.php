<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\IntelPage\Application\IntelPageSender;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function sprintf;

use const SIGINT;
use const SIGTERM;

#[AsCommand(name: 'app:intel-page:run-daemon', description: 'Polls for new messages and transmits them to an IntelPage appliance')]
final class IntelPageSenderDaemonCommand extends Command implements SignalableCommandInterface
{
    use LockableTrait;

    /**
     * @var bool indicate if polling should continue or not (e.g. when script gets terminated)
     */
    private bool $shouldContinue = true;

    public function __construct(
        #[Autowire(param: 'intel_page.transmitter.host')]
        private readonly string $defaultTransmitterHost,
        #[Autowire(param: 'intel_page.transmitter.port')]
        private readonly int $defaultTransmitterPort,
        #[Autowire(param: 'intel_page.time_between_messages')]
        private readonly int $defaultTimeBetweenMessages,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('transmitterHost', 'H', InputOption::VALUE_REQUIRED, '', $this->defaultTransmitterHost)
            ->addOption('transmitterPort', 'p', InputOption::VALUE_REQUIRED, '', $this->defaultTransmitterPort)
            ->addOption('timeBetweenMessages', 't', InputOption::VALUE_REQUIRED, 'in seconds', $this->defaultTimeBetweenMessages)
        ;
    }

    private function processMessages(SymfonyStyle $io, IntelPageSender $transmitter, int $timeBetweenMessages): void
    {
        while ($this->shouldContinue) {
            // TODO poll db

            $capCode = 0;
            $message = 'Hello World';

            if ($io->isVerbose()) {
                $io->writeln('Sending message: ... ');
            }

            // send
            try {
                $transmitter->transmit($capCode, $message);
                // mark as send & send event
            } catch (Exception $e) {
                $io->error($e->getMessage());
            }

            sleep($timeBetweenMessages);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('IntelPage Daemon');

        /** @var string $transmitterHost */
        $transmitterHost = $input->getOption('transmitterHost');
        $transmitterPort = (int) $input->getOption('transmitterPort');
        $timeBetweenMessages = (int) $input->getOption('timeBetweenMessages');
        $transmitter = new IntelPageSender($transmitterHost, $transmitterPort);

        /*if (false === $this->lock('intelpage_daemon_' . sha1($transmitterHost . ': '. $transmitterPort))) {
            $io->error('Failed to acquire lock for this IntelPage appliance. Please ensure no other daemon is running.');
            return Command::FAILURE;
        }*/

        $io->comment('This process sends new IntelPage messages until stopped (press Ctrl+C to stop gracefully)');

        if ($output->isVerbose()) {
            $io->comment(sprintf('Time between messages: %d seconds', $timeBetweenMessages));
        }

        // ##############################
        $this->processMessages($io, $transmitter, $timeBetweenMessages);

        $io->writeln('Daemon stopped gracefully');

        return Command::SUCCESS;
    }

    /**
     * @return array<int>
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal, false|int $previousExitCode = 0): int|false
    {
        $this->shouldContinue = false;

        return false; // tells symfony to not terminate but wait for completion
    }
}
