<?php

declare(strict_types=1);

namespace App\Tests\View\Cli;

use App\Core\UserManagement\Model\User;
use App\View\Cli\EditUserCommand;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use function assert;

#[CoversClass(EditUserCommand::class)]
#[Group('functional')]
final class EditUserCommandTest extends KernelTestCase
{
    public function getEntityManager(): EntityManagerInterface
    {
        return self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $em = $this->getEntityManager();
        $em->persist($this->sampleUser('edituser'));
        $em->flush();
        $em->clear();
    }

    protected function tearDown(): void
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'edituser']);
        assert($user instanceof User);
        $em->remove($user);
        $em->flush();
        $em->clear();
        parent::tearDown();
    }

    private function sampleUser(string $username): User
    {
        $user = new User($username);
        $user->setPassword('');
        $user->setDisplayname('');
        $user->setRoles(['ROLE_TEST']);

        return $user;
    }

    public function testExecuteWithMinimalArguments(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:user:edit');
        $commandTester = new CommandTester($command);
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'edituser']);

        $commandTester->execute([
            'username' => 'edituser',
        ]); // no password or display name

        $commandTester->assertCommandIsSuccessful();
        $result = $em->getRepository(User::class)->findOneBy(['username' => 'edituser']);
        self::assertInstanceOf(User::class, $result);
        self::assertEquals($user, $result);
    }

    public function testExecuteWithAllArguments(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:user:edit');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'username' => 'edituser',
            '--password' => 'newpassword',
            '--displayname' => 'newdisplayname',
            '--addRole' => ['ROLE_ADMIN'],
            '--removeRole' => ['ROLE_TEST'],
        ]);

        $commandTester->assertCommandIsSuccessful();
        $em = $this->getEntityManager();
        $result = $em->getRepository(User::class)->findOneBy(['username' => 'edituser']);
        self::assertInstanceOf(User::class, $result);
        self::assertSame('newdisplayname', $result->getDisplayname());
        self::assertNotSame('', $result->getPassword());
        self::assertNotSame('newpassword', $result->getPassword());
        self::assertContains('ROLE_ADMIN', $result->getRoles());
        self::assertNotContains('ROLE_TEST', $result->getRoles());
        self::assertCount(2, $result->getRoles());
    }
}
