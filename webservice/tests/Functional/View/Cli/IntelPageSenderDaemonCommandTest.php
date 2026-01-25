<?php

declare(strict_types=1);

namespace App\Tests\Functional\View\Cli;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class IntelPageSenderDaemonCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('app:intel-page:run-daemon');
        $commandTester = new CommandTester($command);
        // $commandTester->execute([]); // can not test because it can't be stopped from phpunit

        // $commandTester->assertCommandIsSuccessful();
        Assert::markTestIncomplete('command can\'t be tested yet, as phpunit can\'t kill it.');
    }
}
