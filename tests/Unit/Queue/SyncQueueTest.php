<?php
namespace Piplup\ImageX\Tests\Unit\Queue;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Queue\SyncQueue;

class SyncQueueTest extends TestCase
{
    public function testPushAndDispatchOne()
    {
        $queue = new SyncQueue();
        $called = false;
        $queue->push(function() use (&$called) { $called = true; });

        $this->assertTrue($queue->dispatchOne());
        $this->assertTrue($called);
        $this->assertFalse($queue->dispatchOne());
    }

    public function testDispatchAll()
    {
        $queue = new SyncQueue();
        $count = 0;
        $queue->push(function() use (&$count) { $count++; });
        $queue->push(function() use (&$count) { $count++; });

        $this->assertSame(2, $queue->dispatchAll());
        $this->assertSame(2, $count);
    }
}
