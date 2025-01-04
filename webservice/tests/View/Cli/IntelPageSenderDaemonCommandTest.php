<?php

declare(strict_types=1);

namespace App\Tests\View\Cli;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('commands'), Group('intel-page')]
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
