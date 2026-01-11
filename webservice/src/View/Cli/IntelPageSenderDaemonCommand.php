<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\IntelPage\Application\IntelPageTransmitter;
use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\IntelPage\Application\SendPagerMessageService;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\TransportContract\Port\TransportManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use function sprintf;
use const SIGINT;
use const SIGTERM;

/**
 * Long-running process that polls the database for new pager messages to be sent and sends them to the transmitter.
 */
#[AsCommand(name: 'app:intel-page:run-daemon', description: 'Polls for new messages and transmits them to an IntelPage appliance')]
final class IntelPageSenderDaemonCommand extends Command implements SignalableCommandInterface
{
    // use LockableTrait;

    /**
     * @var bool indicate if polling should continue or not (e.g. when script gets terminated)
     */
    private bool $shouldContinue = true;

    /**
     * @param int $defaultMicrosecondsAfterNoNewMessage Time to sleep before checking for a new message after no message was found in microseconds
     */
    public function __construct(
        #[Autowire(param: 'intel_page.transmitter.host')]
        private readonly string $defaultTransmitterHost,
        #[Autowire(param: 'intel_page.transmitter.port')]
        private readonly int $defaultTransmitterPort,
        #[Autowire(param: 'intel_page.seconds_between_messages')]
        private readonly int $defaultTimeBetweenMessages,
        #[Autowire(param: 'intel_page.seconds_after_error')]
        private readonly int $defaultTimeAfterError,
        #[Autowire(param: 'intel_page.microseconds_after_no_new_message')]
        private readonly int $defaultMicrosecondsAfterNoNewMessage,
        private readonly EntityManagerInterface $em,
        private readonly TransportManager $transportManager,
        private readonly ?MessageBusInterface $eventBus = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('transport-key', InputArgument::REQUIRED, 'key of transport')
            ->addOption('timeBetweenMessages', 't', InputOption::VALUE_REQUIRED, 'in seconds', $this->defaultTimeBetweenMessages)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('IntelPage Daemon');

        // Set up
        $transport = $this->transportManager->transportWithKey($input->getArgument('transport-key'));
        if (!$transport instanceof IntelPageTransport) {
            $io->error('No IntelPage transport ('.IntelPageTransport::class.') with the provided key found.');

            return self::INVALID;
        }

        $sender = $this->initSender($transport);
        $timeBetweenMessages = (int) $input->getOption('timeBetweenMessages');

        // Inform user about what's going on:
        $io->comment('This process sends new IntelPage messages until stopped (press Ctrl+C to stop gracefully)');
        if ($output->isVerbose()) {
            $io->comment(sprintf('Only messages send to transport with key: %s', $transport->key()));
            $io->comment(sprintf('Time between messages: %d seconds', $timeBetweenMessages));
        }

        // Do:
        $this->processMessages($io, $sender, $transport->key(), $timeBetweenMessages);

        // Exit:
        $io->writeln('Daemon stopped gracefully');

        return Command::SUCCESS;
    }

    /**
     * List of process signals to terminate this script gracefully.
     *
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

    /**
     * Extracts the final parameters from the different
     * configuration paths and instantiates a
     * SendPagerMessageService.
     */
    private function initSender(IntelPageTransport $transport): SendPagerMessageService
    {
        [$host, $port] = $transport->configuredTransmitter();
        $transmitterHost = $host ?? $this->defaultTransmitterHost;
        $transmitterPort = $port ?? $this->defaultTransmitterPort;
        $transmitter = new IntelPageTransmitter($transmitterHost, $transmitterPort);

        return new SendPagerMessageService($this->em, $transmitter, $this->eventBus, $this->logger);

        /*
             TODO future improvement
             if (false === $this->lock('intelpage_daemon_' . sha1($transmitterHost . ': '. $transmitterPort))) {
                $io->error('Failed to acquire lock for this IntelPage appliance. Please ensure no other daemon is running.');
                return Command::FAILURE;
             }
        */
    }

    private function processMessages(
        SymfonyStyle $io,
        SendPagerMessageService $sender,
        string $transportKey,
        int $timeBetweenMessages,
    ): void {
        while ($this->shouldContinue) {
            $message = $sender->nextMessageToSend($transportKey);
            if (!$message instanceof PagerMessage) {
                // -- there is no new message
                usleep($this->defaultMicrosecondsAfterNoNewMessage);
                continue;
            }

            // -- there is a message, now try to send it:
            try {
                if ($io->isVerbose()) {
                    $io->writeln('Sending message: '.$message->getId());
                }

                $sender->send($message);

                // after we send the message, wait before trying the next one:
                sleep($timeBetweenMessages);
            } catch (Exception $e) {
                $io->error($e->getMessage());
                sleep($this->defaultTimeAfterError);
            }
        }
    }
}
