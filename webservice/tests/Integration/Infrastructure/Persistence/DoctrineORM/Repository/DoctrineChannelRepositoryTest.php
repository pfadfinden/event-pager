<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrineChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(DoctrineChannelRepository::class)]
final class DoctrineChannelRepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    public function testPersistNewChannel(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new DoctrineChannelRepository($em);

        $channel = $this->newChannel();

        // Act
        $sut->persist($channel);

        $em->flush();
        $em->clear();

        // Assert
        /** @var Channel $result */
        $result = $em->find(Channel::class, Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'));

        self::assertEquals('All', $result->getName());

        // Cleanup
        $em->remove($result);
        $em->flush();
    }

    public function testGetById(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();

        $channel = $this->newChannel();

        $em = $container->get(EntityManagerInterface::class);
        $em->persist($channel);
        $em->flush();
        $em->clear();

        $sut = new DoctrineChannelRepository($em);

        // Act
        $result = $sut->getById(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'));

        // Assert
        self::assertInstanceOf(Channel::class, $result);
        self::assertSame('All', $result->getName());

        // Cleanup
        $em->remove($result);
        $em->flush();
    }

    public function testRemoveById(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();

        $channel = $this->newChannel();

        $em = $container->get(EntityManagerInterface::class);
        $em->persist($channel);
        $em->flush();
        $em->clear();

        $sut = new DoctrineChannelRepository($em);

        $channel1 = $sut->getById(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'));
        self::assertInstanceOf(Channel::class, $channel1);

        // Act
        $sut->remove($channel1);
        $em->flush();

        // Assert
        self::assertNull($sut->getById(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C')));
    }

    private function newChannel(): Channel
    {
        return new Channel(
            Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'),
            'All',
            CapCode::fromInt(3223),
        );
    }
}
