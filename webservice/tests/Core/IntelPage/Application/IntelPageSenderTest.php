<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Application {
    /**
     * Override fsockopen() in the App\Tests\Core\IntelPage\Application namespace when testing.
     *
     * @return null
     */
    function fsockopen()
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

namespace App\Tests\Core\IntelPage\Application {
    use App\Core\IntelPage\Application\IntelPageSender;
    use PHPUnit\Framework\Attributes\Group;
    use PHPUnit\Framework\TestCase;

    #[Group('unit')]
    final class IntelPageSenderTest extends TestCase
    {
        public function testSend(): void
        {
            $sender = new IntelPageSender('pager_transmitter_stub', 6000);
            $sender->transmit(9001, 'Hello World');
            $this->expectNotToPerformAssertions();
        }
    }
}
