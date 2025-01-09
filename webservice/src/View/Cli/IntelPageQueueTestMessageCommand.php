<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\PagerMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Ulid;

#[AsCommand(name: 'app:intel-page:queue-msg', description: '...')]
/**
 * TODO To be removed, for initial testing only.
 */
final class IntelPageQueueTestMessageCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('cap', InputArgument::REQUIRED, '')
            ->addArgument('message', InputArgument::REQUIRED, 'the message body')
            ->addArgument('priority', InputArgument::REQUIRED, 'priority of message')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('IntelPage Queue Test Message');

        $cap = CapCode::fromString($input->getArgument('cap'));
        $message = PagerMessage::new(Ulid::fromString(Ulid::generate()), $cap, $input->getArgument('message'), (int) $input->getArgument('priority'));
        $this->em->persist($message);
        $this->em->flush();

        $io->writeln('Pager Message queued');

        return Command::SUCCESS;
    }
}
