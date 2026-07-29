<?php

declare(strict_types=1);

namespace App\Tests\Functional\View\Cli;

use App\Core\UserManagement\Model\User;
use App\View\Cli\ListUsersCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ListUsersCommand::class)]
final class ListUsersCommandTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $localUser = new User('local-user');
        $localUser->setPassword('hashed-password');

        $ssoUser = new User('sso-user');
        $ssoUser->setExternalId('keycloak-sub-123');

        $hybridUser = new User('hybrid-user');
        $hybridUser->setPassword('hashed-password');
        $hybridUser->setExternalId('keycloak-sub-456');

        $noLoginUser = new User('no-login-user');

        $em->persist($localUser);
        $em->persist($ssoUser);
        $em->persist($hybridUser);
        $em->persist($noLoginUser);
        $em->flush();
        $em->clear();
    }

    public function testExecuteListsAllUsersWithLoginType(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:user:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('local-user', $output);
        self::assertStringContainsString('sso-user', $output);
        self::assertStringContainsString('hybrid-user', $output);
        self::assertStringContainsString('no-login-user', $output);

        self::assertStringContainsString('Local', $output);
        self::assertStringContainsString('SSO', $output);
        self::assertStringContainsString('SSO + Local', $output);
        self::assertStringContainsString('None', $output);
    }
}
