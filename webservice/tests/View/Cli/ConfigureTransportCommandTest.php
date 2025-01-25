<?php

declare(strict_types=1);

namespace App\Tests\View\Cli;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\View\Cli\ConfigureTransportCommand;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ConfigureTransportCommand::class)]
#[Group('functional')]
final class ConfigureTransportCommandTest extends KernelTestCase
{
    use ResetDatabase;

    public function getEntityManager(): EntityManagerInterface
    {
        $container = self::$kernel->getContainer();

        return $container->get('doctrine.orm.entity_manager');
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $em = $this->getEntityManager();
        $em->persist($this->sampleConfiguration('test-dummy', false));
        $em->flush();
        $em->clear();
    }

    public function testCanExecuteWithMinimalArguments(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:transport:configure');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'key' => 'test-dummy',
            'transport' => '\App\Tests\Mock\DummyTransport',
            'title' => 'Hello App',
        ]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('test-dummy', $output);

        $em = $this->getEntityManager();
        $result = $em->find(TransportConfiguration::class, 'test-dummy');
        self::assertInstanceOf(TransportConfiguration::class, $result);
        self::assertSame('Hello App', $result->getTitle());
        self::assertFalse($result->isEnabled());
    }

    public function testCanExecuteWithAllArguments(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:transport:configure');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'key' => 'test-dummy',
            'transport' => '\App\Tests\Mock\DummyTransport',
            'title' => 'Hello App',
            'vendorSpecificJson' => '{"help":1}',
            '--enable' => true,
        ]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('test-dummy', $output);

        $em = $this->getEntityManager();
        $result = $em->find(TransportConfiguration::class, 'test-dummy');
        self::assertInstanceOf(TransportConfiguration::class, $result);
        self::assertSame('Hello App', $result->getTitle());
        self::assertTrue($result->isEnabled());
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
