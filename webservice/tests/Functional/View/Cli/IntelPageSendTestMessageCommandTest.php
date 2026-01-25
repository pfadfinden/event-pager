<?php

declare(strict_types=1);

namespace App\Tests\Functional\View\Cli;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

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
