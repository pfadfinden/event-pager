<?php

namespace App\Tests\Entity;

use App\Entity\CapCode;
use PHPUnit\Framework\TestCase;

class CapCodeTest extends TestCase
{
    public function testCreateValidCapCode(): void
    {
        try {
            new CapCode(3);
        } catch (\InvalidArgumentException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function testCreateInvalidCapCode(): void
    {
        try {
            new CapCode(-1);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('Able to assign an invalid cap code but shouldn\'t!');
    }
}
