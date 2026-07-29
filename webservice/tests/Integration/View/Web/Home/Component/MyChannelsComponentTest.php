<?php

declare(strict_types=1);

namespace App\Tests\Integration\View\Web\Home\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Model\Person;
use App\Core\UserManagement\Model\User;
use App\View\Web\Home\Component\MyChannelsComponent;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(MyChannelsComponent::class)]
#[Group('integration')]
final class MyChannelsComponentTest extends KernelTestCase
{
    use ResetDatabase;

    public function testReturnsNullWhenLoggedInUserHasNoLinkedRecipient(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $queryBus = $container->get(QueryBus::class);

        $user = new User('unlinked-user');
        $em->persist($user);
        $em->flush();

        $security = self::createMock(Security::class);
        $security->expects(self::once())->method('getUser')->willReturn($user);

        $component = new MyChannelsComponent($queryBus, $security);

        self::assertNull($component->getRecipient());
    }

    public function testReturnsRecipientDetailWithTransportConfigurationWhenLinked(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $queryBus = $container->get(QueryBus::class);

        $person = new Person('Linked Person');
        $person->addTransportConfiguration('ntfy');
        $em->persist($person);

        $user = new User('linked-user');
        $user->setRecipient($person);
        $em->persist($user);
        $em->flush();

        $security = self::createMock(Security::class);
        $security->expects(self::once())->method('getUser')->willReturn($user);

        $component = new MyChannelsComponent($queryBus, $security);
        $recipient = $component->getRecipient();

        self::assertNotNull($recipient);
        self::assertSame($person->getId()->toString(), $recipient->id);
        self::assertCount(1, $recipient->transportConfigurations);
        self::assertSame('ntfy', array_values($recipient->transportConfigurations)[0]->key);
    }
}
