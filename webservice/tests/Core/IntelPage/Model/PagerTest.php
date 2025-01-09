<?php

namespace App\Tests\Entity;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\ChannelCapAssignment;
use App\Core\IntelPage\Model\IndividualCapAssignment;
use App\Core\IntelPage\Model\NoCapAssignment;
use App\Core\IntelPage\Model\Pager;
use PHPUnit\Framework\TestCase;

class PagerTest extends TestCase
{
    public function testCapAssignment(): void
    {
        $pager = new Pager('Pager 1', 3);
        $channel = new Channel();

        $pager->assignCap(0, new NoCapAssignment())
            ->assignCap(1, new IndividualCapAssignment(false, false, new CapCode(1)))
            ->assignCap(7, new ChannelCapAssignment($channel));

        $assignments = $pager->getCapAssignments();
        $this->assertTrue($assignments[0] instanceof NoCapAssignment);
        $this->assertTrue($assignments[1] instanceof IndividualCapAssignment);
        $this->assertTrue($assignments[7] instanceof ChannelCapAssignment);
    }

    public function testSlotOutOfBoundsUpper(): void
    {
        $pager = new Pager('Pager 2', 2);

        try {
            $pager->assignCap(8, new NoCapAssignment());
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
            $pager->assignCap(-1, new NoCapAssignment());
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);

            return;
        }

        $this->fail();
    }
}
