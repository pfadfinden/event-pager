<?php

declare(strict_types=1);

namespace App\Tests\View\Cli;

use App\Core\UserManagement\Model\User;
use App\View\Cli\AddUserCommand;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use function assert;

#[CoversClass(AddUserCommand::class)]
#[Group('functional')]
class AddUserCommandTest extends KernelTestCase
{
    public function getEntityManager(): EntityManagerInterface
    {
        return self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    #[Override]
    protected function tearDown(): void
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'newuser']);
        assert($user instanceof User);
        $em->remove($user);
        $em->flush();
        $em->clear();
        parent::tearDown();
    }

    public function testCanExecuteWithMinimalArguments(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:user:add');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'username' => 'newuser',
        ]); // no password or display name

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('newuser', $output);

        $em = $this->getEntityManager();
        $result = $em->getRepository(User::class)->findOneBy(['username' => 'newuser']);

        self::assertInstanceOf(User::class, $result);
        self::assertSame('newuser', $result->getDisplayname());
        self::assertNotNull($result->getId());
        self::assertNotEmpty($result->getPassword());
        self::assertNotSame('', $result->getPassword());
        self::assertCount(1, $result->getRoles());
        self::assertContains('ROLE_USER', $result->getRoles());
    }

    public function testCanExecuteWithAllArguments(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:user:add');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'username' => 'newuser',
            '--password' => 'testpassword',
            '--displayName' => 'Test User',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('newuser', $output);

        $em = $this->getEntityManager();
        $result = $em->getRepository(User::class)->findOneBy(['username' => 'newuser']);

        self::assertInstanceOf(User::class, $result);
        self::assertSame('Test User', $result->getDisplayname());
        self::assertNotNull($result->getId());
        self::assertNotEmpty($result->getPassword());
        self::assertCount(1, $result->getRoles());
        self::assertContains('ROLE_USER', $result->getRoles());
    }
}
