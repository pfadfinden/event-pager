<?php

declare(strict_types=1);

namespace App\Tests\Integration\View\Cli;

use App\Core\SendMessage\Model\IncomingMessage;
use App\View\Cli\SendMessageCommand;
use Doctrine\ORM\EntityManagerInterface;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Ulid;

#[CoversClass(SendMessageCommand::class)]
final class SendMessageCommandTest extends KernelTestCase
{
    /**
     * @return Iterator<string, array{string, int, array<string>, string, array<string, (array<string> | int | string)>}>
     */
    public static function commandArgsProvider(): Iterator
    {
        $alternateFrom = Ulid::generate();
        $to = Ulid::generate();
        $to2 = Ulid::generate();
        yield 'minimal' => [
            'Hello World Minimal',
            1,
            [$to],
            SendMessageCommand::SEND_BY_CLI_ID,
            [
                'message' => 'Hello World Minimal',
                '--to' => [$to],
            ],
        ];
        yield 'full' => [
            'Hello World Full',
            3,
            [$to, $to2],
            $alternateFrom,
            [
                'message' => 'Hello World Full',
                '--to' => [$to, $to2],
                '--priority' => 3,
                '--from' => $alternateFrom,
            ],
        ];
        yield 'short' => [
            'Hello World Short',
            3,
            [$to],
            $alternateFrom,
            [
                'message' => 'Hello World Short',
                '-t' => [$to],
                '-p' => 3,
                '-f' => $alternateFrom,
            ],
        ];
    }

    /**
     * @param string[]                           $to
     * @param array<string, string|int|string[]> $args
     */
    #[DataProvider('commandArgsProvider')]
    public function testExecute(
        string $content, int $prio, array $to, string $from, array $args,
    ): void {
        // Arrange
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:message:send');
        $commandTester = new CommandTester($command);

        // Act
        $commandTester->execute($args);

        // Assert
        $commandTester->assertCommandIsSuccessful();

        /** @var EntityManagerInterface $em */
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager();
        $msg = $em->getRepository(IncomingMessage::class)->findOneBy(['by' => $from], ['sentOn' => 'DESC']);
        self::assertInstanceOf(IncomingMessage::class, $msg);
        self::assertSame($content, $msg->content);
        self::assertSame($prio, $msg->priority);
        self::assertSame($to, array_map(fn (Ulid $ulid): string => $ulid->toString(), $msg->to));

        // Clean
        $em->remove($msg);
    }
}
