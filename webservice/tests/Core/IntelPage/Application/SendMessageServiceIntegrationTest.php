<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Application;

use App\Core\IntelPage\Application\SendPagerMessageService;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Port\IntelPageTransmitterInterface;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Group('integration'), Group('integration.database')]
#[CoversClass(SendPagerMessageService::class)]
final class SendMessageServiceIntegrationTest extends KernelTestCase
{
    use ResetDatabase;

    public function testLimitsAge(): void
    {
        // Arrange
        $messageTooOld = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Too Old', 1,
            Instant::of(5 * 60)
        );

        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $em->persist($messageTooOld);
        $em->flush();
        $em->clear();

        // T= 10minutes
        DefaultClock::set(new FixedClock(Instant::of(60 * 10)));

        $transmitter = self::createStub(IntelPageTransmitterInterface::class);
        $eventBus = self::createStub(MessageBusInterface::class);

        $sut = new SendPagerMessageService($em, $transmitter, $eventBus);

        // Act

        $res = $sut->nextMessageToSend('defaultA');

        // Assert
        self::assertNull($res);
    }

    public function testOlderBeforeNew(): void
    {
        // Arrange
        $messageA = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Perfect', 2,
            Instant::of(5 * 60 + 1)
        );
        $messageA->failedToSend();
        $messageB = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Too Young', 2,
            Instant::of(8 * 60)
        );

        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $em->persist($messageA);
        $em->persist($messageB);
        $em->flush();
        $em->clear();

        // T= 10minutes
        DefaultClock::set(new FixedClock(Instant::of(60 * 10)));

        $transmitter = self::createStub(IntelPageTransmitterInterface::class);
        $eventBus = self::createStub(MessageBusInterface::class);

        $sut = new SendPagerMessageService($em, $transmitter, $eventBus);

        // Act

        $res = $sut->nextMessageToSend('defaultA');

        // Assert
        self::assertInstanceOf(PagerMessage::class, $res);
        self::assertEquals('Perfect', $res->getMessage());
    }

    public function testLimitsRetries(): void
    {
        // Arrangex
        $messageA = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Perfect', 2,
            Instant::of(7 * 60)
        );
        $messageA->failedToSend();
        $messageB = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Too Many Retries', 2,
            Instant::of(6 * 60)
        );
        $messageB->failedToSend();
        $messageB->failedToSend();
        $messageC = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Perfect', 2,
            Instant::of(7 * 60)
        );
        $messageC->markSend();

        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $em->persist($messageA);
        $em->persist($messageB);
        $em->flush();
        $em->clear();

        // T= 10minutes
        DefaultClock::set(new FixedClock(Instant::of(60 * 10)));

        $transmitter = self::createStub(IntelPageTransmitterInterface::class);
        $eventBus = self::createStub(MessageBusInterface::class);

        $sut = new SendPagerMessageService($em, $transmitter, $eventBus);

        // Act

        $res = $sut->nextMessageToSend('defaultA');

        // Assert
        self::assertInstanceOf(PagerMessage::class, $res);
        self::assertEquals('Perfect', $res->getMessage());
    }

    public function testDoesNotResend(): void
    {
        // Arrange
        $messageC = new PagerMessage(
            Ulid::fromString(Ulid::generate()), 'defaultA', CapCode::fromInt(8001),
            'Perfect', 2,
            Instant::of(7 * 60)
        );
        $messageC->markSend();

        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $em->persist($messageC);
        $em->flush();
        $em->clear();

        // T= 10minutes
        DefaultClock::set(new FixedClock(Instant::of(60 * 10)));

        $transmitter = self::createStub(IntelPageTransmitterInterface::class);
        $eventBus = self::createStub(MessageBusInterface::class);

        $sut = new SendPagerMessageService($em, $transmitter, $eventBus);

        // Act

        $res = $sut->nextMessageToSend('defaultA');

        // Assert
        self::assertNull($res);
    }
}
