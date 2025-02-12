<?php

declare(strict_types= 1);

namespace App\Tests\View\Cli;

use App\Core\UserManagement\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use App\View\Cli\DeleteUserCommand;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;


#[CoversClass(DeleteUserCommand::class)]
#[Group('functional')]
final class DeleteUserCommandTest extends KernelTestCase
{
    public function getEntityManager(): EntityManagerInterface
    {
        return self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $em = $this->getEntityManager();
        $em->persist($this->sampleUser('testuser'));
        $em->flush();
        $em->clear();
    }

    protected function tearDown(): void
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username'=> 'testuser']);
        if ($user === null) {
            return;
        }

        $em->remove($user);
        $em->flush();
        $em->clear();
        self::ensureKernelShutdown();
    }

    private function sampleUser(string $username): User
    {
        $user = new User;
        $user->setUsername($username);
        $user->setPassword('');
        $user->setDisplayname('');
        return $user;
    }

    public function testExecuteDeletUserCommand(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:user:delete');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'username' => 'testuser',
            ]);

        $commandTester->assertCommandIsSuccessful();
        $em = $this->getEntityManager();
        $result = $em->getRepository(User::class)->findOneBy(['username' => 'testuser']);
        self::assertNull($result);
    }
}