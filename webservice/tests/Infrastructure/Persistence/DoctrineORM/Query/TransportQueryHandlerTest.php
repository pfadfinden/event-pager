<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\Transport;
use App\Infrastructure\Persistence\DoctrineORM\Query\TransportQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(TransportQueryHandler::class)]
#[CoversClass(Transport::class)]
#[Group('integration'), Group('integration.database')]
final class TransportQueryHandlerTest extends KernelTestCase
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

        $sut = new TransportQueryHandler($em);

        $query = Transport::withKey('test-disabled');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertNotNull($result);
        self::assertSame('test-disabled', $result->getKey());
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
