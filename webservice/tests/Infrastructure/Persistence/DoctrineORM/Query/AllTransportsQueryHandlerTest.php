<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\AllTransports;
use App\Infrastructure\Persistence\DoctrineORM\Query\AllTransportsQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(AllTransportsQueryHandler::class)]
#[CoversClass(AllTransports::class)]
#[Group('integration'), Group('integration.database')]
final class AllTransportsQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist($this->sampleConfiguration('test-disabled', false));
        $em->persist($this->sampleConfiguration('test-enabled', true));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveAllTransportsWithoutFilter(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new AllTransportsQueryHandler($em);

        $query = AllTransports::withoutFilter();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(TransportConfiguration::class, $result);
    }

    public function testCanRetrieveAllEnabledTransports(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new AllTransportsQueryHandler($em);

        $query = AllTransports::thatAreEnabled();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(TransportConfiguration::class, $result);
        self::assertSame('test-enabled', $result[0]->getKey());
    }

    public function testCanRetrieveAllDisabledTransports(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new AllTransportsQueryHandler($em);

        $query = AllTransports::thatAreDisabled();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(TransportConfiguration::class, $result);
        self::assertSame('test-disabled', $result[0]->getKey());
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
