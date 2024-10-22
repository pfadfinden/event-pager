<?php

namespace App\Tests\Entity;

use App\Entity\CapCode;
use App\Entity\Channel;
use App\Entity\ChannelCapAssignment;
use App\Entity\IndividualCapAssignment;
use App\Entity\NoCapAssignment;
use App\Entity\Pager;
use PHPUnit\Framework\TestCase;

class PagerTest extends TestCase
{
    public function testCapAssignment(): void
    {
        $pager = new Pager('Pager 1', 3);
        $channel = new Channel();

        $pager->assignCap(0, new NoCapAssignment())
            ->assignCap(1, new IndividualCapAssignment(false, false, new CapCode(1)))
            ->assignCap(2, new ChannelCapAssignment($channel));

        $assignments = $pager->getCapAssignments();
        $this->assertTrue($assignments[0] instanceof NoCapAssignment);
        $this->assertTrue($assignments[1] instanceof IndividualCapAssignment);
        $this->assertTrue($assignments[2] instanceof ChannelCapAssignment);
    }

    public function testSlotOutOfBoundsUpper(): void
    {
        $pager = new Pager('Pager 2', 2);

        try {
            $pager->assignCap(10, new NoCapAssignment());
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('Assigned a CapAssignment out of bounds but shouldn\'t be able to!');
    }

    public function testSlotOutOfBoundsLower(): void
    {
        $pager = new Pager('Pager 3', 3);

        try {
            $pager->assignCap(-10, new NoCapAssignment());
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);

            return;
        }

        $this->fail();
    }
}
