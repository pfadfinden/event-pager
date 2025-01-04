<?php

declare(strict_types=1);

namespace App\Tests\View\Cli;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('commands'), Group('intel-page')]
final class IntelPageSendTestMessageCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('app:intel-page:send-message');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'cap' => 9001,
            'message' => 'Hello World!',
        ], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        Assert::assertStringContainsString('IntelPage: Test Message Utility', $output);
        Assert::assertStringContainsString('CAP Code: 9001', $output);
        Assert::assertStringContainsString('Message: Hello World!', $output);
    }
}
