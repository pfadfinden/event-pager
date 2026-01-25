<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\IntelPage\Application {
    /**
     * Override fsockopen() in the App\Tests\Core\IntelPage\Application namespace when testing.
     */
    function fsockopen(): null
    {
        return null;
    }

    /**
     * Override fwrite() in the App\Tests\Core\IntelPage\Application namespace when testing.
     */
    function fwrite(mixed $stream, string $data, ?int $length): int
    {
        $pattern = '/^\d+\r[\x00-\x7F]+\r\r$/';
        if (false === preg_match($pattern, $data)) {
            throw new \UnexpectedValueException('fwrite received wrong data');
        }

        return \strlen($data);
    }
}

namespace App\Tests\Unit\Core\IntelPage\Application {
    use App\Core\IntelPage\Application\IntelPageTransmitter;
    use App\Core\IntelPage\Exception\IntelPageTransmitterNotAvailable;
    use App\Core\IntelPage\Model\CapCode;
    use PHPUnit\Framework\TestCase;

    final class IntelPageTransmitterTest extends TestCase
    {
        public function testSend(): void
        {
            $sender = new IntelPageTransmitter('pager_transmitter_stub', 6000);
            $sender->transmit(CapCode::fromInt(9001), 'Hello World');
            $this->expectNotToPerformAssertions();
        }

        public function testCanSendOnlyAscii(): void
        {
            $this->expectException(\UnexpectedValueException::class);
            $sender = new IntelPageTransmitter('pager_transmitter_stub', 6000);
            $sender->transmit(CapCode::fromInt(9001), 'Hello Wörld');
        }

        public function testThrowsNotAvailableExceptionIFTransmitterUnreachable(): void
        {
            $this->expectException(IntelPageTransmitterNotAvailable::class);
            $sender = new IntelPageTransmitter('notallocated.localhost', 6000);
            $sender->transmit(CapCode::fromInt(9001), 'Hello World');
        }
    }
}
