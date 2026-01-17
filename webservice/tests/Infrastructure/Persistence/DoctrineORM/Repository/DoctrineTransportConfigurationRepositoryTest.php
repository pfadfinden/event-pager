<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrineTransportConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(DoctrineTransportConfigurationRepository::class)]
#[Group('integration'), Group('integration.database')]
final class DoctrineTransportConfigurationRepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    public function testPersistNewTransportConfiguration(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new DoctrineTransportConfigurationRepository($em);

        $configuration = $this->newMinimalTransportConfiguration();

        // Act
        $sut->persist($configuration);

        $em->flush();
        $em->clear();

        // Assert
        /** @var TransportConfiguration $result */
        $result = $em->find(TransportConfiguration::class, 'test-dummy');

        self::assertEquals('Hello World', $result->getTitle());

        // Cleanup
        $em->remove($result);
        $em->flush();
    }

    public function testGetByKey(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();

        $configuration = $this->newMinimalTransportConfiguration();

        $em = $container->get(EntityManagerInterface::class);
        $em->persist($configuration);
        $em->flush();
        $em->clear();

        $sut = new DoctrineTransportConfigurationRepository($em);

        // Act
        $result = $sut->getByKey('test-dummy');

        // Assert
        self::assertInstanceOf(TransportConfiguration::class, $result);
        self::assertSame('Hello World', $result->getTitle());

        // Cleanup
        $em->remove($result);
        $em->flush();
    }

    public function testRemoveByKey(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();

        $configuration = $this->newMinimalTransportConfiguration();

        $em = $container->get(EntityManagerInterface::class);
        $em->persist($configuration);
        $em->flush();
        $em->clear();

        $sut = new DoctrineTransportConfigurationRepository($em);

        // Act
        $sut->removeByKey('test-dummy');
        $em->flush();

        // Assert
        self::assertNull($sut->getByKey('test-dummy'));
    }

    public function testRemoveByKeyDosNotFailIfNotFound(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new DoctrineTransportConfigurationRepository($em);

        // Act
        $sut->removeByKey('test-dummy');
        $em->flush();

        // Assert
        self::assertNull($sut->getByKey('test-dummy'));
    }

    private function newMinimalTransportConfiguration(): TransportConfiguration
    {
        return new TransportConfiguration(
            'test-dummy',
            '\App\Tests\Mock\DummyTransport',
            'Hello World'
        );
    }
}
