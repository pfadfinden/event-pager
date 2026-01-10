<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\MessageRecipient\Command\CreateRecipient;
use App\Core\MessageRecipient\Handler\CreateRecipientHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Role;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(CreateRecipientHandler::class)]
#[Group('integration'), Group('integration.database')]
final class CreateRecipientHandlerIntegrationTest extends KernelTestCase
{
    use ResetDatabase;

    /*protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist($this->sampleConfiguration('test-disabled', false));
        $em->persist($this->sampleConfiguration('test-enabled', true));
        $em->flush();
        $em->clear();
    }*/

    public function testCanRetrieveAllTransportsWithoutFilter(): void
    {
        // Arrange
        $container = static::getContainer();
        $cmdBus = $container->get(CommandBus::class);

        $command = new CreateRecipient(
            Ulid::generate(),
            'role',
            'Role A',
        );
        // Act
        $cmdBus->do($command);

        // Assert
        $em = $container->get(EntityManagerInterface::class);
        $em->clear();
        $roles = $em->getRepository(AbstractMessageRecipient::class)->findBy(['name' => 'Role A']);
        self::assertCount(1, $roles);
        self::assertInstanceOf(Role::class, $roles[0]);
        self::assertSame('Role A', $roles[0]->getName());
    }
}
