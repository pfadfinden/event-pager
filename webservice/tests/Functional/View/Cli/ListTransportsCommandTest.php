<?php

declare(strict_types=1);

namespace App\Tests\Functional\View\Cli;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\View\Cli\ListTransportsCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ListTransportsCommand::class)]
final class ListTransportsCommandTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->persist($this->sampleConfiguration('test-disabled', false));
        $em->persist($this->sampleConfiguration('test-enabled', true));
        $em->flush();
        $em->clear();
    }

    public function testExecute(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:transport:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('test-disabled', $output);
        self::assertStringContainsString('test-enabled', $output);
    }

    public function testExecuteWithEnabledFlag(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:transport:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--enabled' => true]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('test-enabled', $output);
        self::assertStringNotContainsString('test-disabled', $output);
    }

    public function testExecuteWithNoEnabledFlag(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:transport:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--no-enabled' => true]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('test-disabled', $output);
        self::assertStringNotContainsString('test-enabled', $output);
    }

    private function sampleConfiguration(string $key, bool $enabled): TransportConfiguration
    {
        $transportConfiguration = new TransportConfiguration(
            $key,
            '\App\Tests\Mock\DummyTransport',
            'Hello World'
        );
        $transportConfiguration->setEnabled($enabled);

        return $transportConfiguration;
    }
}
