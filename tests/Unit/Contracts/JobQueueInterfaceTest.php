<?php
namespace Piplup\ImageX\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Queue\SyncQueue;
use Piplup\ImageX\Contracts\JobQueueInterface;

class JobQueueInterfaceTest extends TestCase
{
    public function testSyncQueueImplementsInterface()
    {
        $queue = new SyncQueue();
        $this->assertInstanceOf(JobQueueInterface::class, $queue);
    }
}
