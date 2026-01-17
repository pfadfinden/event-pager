<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\SendMessage\Command\SendMessage;
use App\Core\SendMessage\Handler\SendMessageHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:message:send',
    description: 'send a message through the service',
    aliases: ['send']
)]
final class SendMessageCommand extends Command
{
    public const string SEND_BY_CLI_ID = '01JDC3CRJ0VEFV6FMRK0R5AEMR';
    public const string ARG_MESSAGE = 'message';
    public const string OPT_FROM = 'from';
    public const string OPT_TO = 'to';
    public const string OPT_PRIO = 'priority';

    public function __construct(private readonly SendMessageHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: self::ARG_MESSAGE,
                mode: InputArgument::REQUIRED,
                description: 'Message body'
            )
            ->addOption(
                name: self::OPT_FROM,
                shortcut: 'f',
                mode: InputOption::VALUE_REQUIRED,
                default: self::SEND_BY_CLI_ID,
                description: 'ULID of sender'
            )
            ->addOption(
                name: self::OPT_TO,
                shortcut: 't',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'ULID of recipients'
            )
            ->addOption(
                name: self::OPT_PRIO,
                shortcut: 'p',
                mode: InputOption::VALUE_REQUIRED,
                description: 'sets the message priority',
                default: 1,
                suggestedValues: [1],
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = new SendMessage(
            $input->getArgument(self::ARG_MESSAGE),
            $input->getOption(self::OPT_FROM),
            (int) $input->getOption(self::OPT_PRIO),
            $input->getOption(self::OPT_TO),
        );

        $this->handler->__invoke($command);

        return Command::SUCCESS;
    }
}
