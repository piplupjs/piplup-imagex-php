<?php
namespace Piplup\ImageX\Tests\Unit\Queue;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Queue\SymfonyProcessQueue;

class FakeProcess
{
    public array $cmd;
    public bool $started = false;

    public function __construct(array $cmd)
    {
        $this->cmd = $cmd;
    }

    public function start(): void
    {
        $this->started = true;
    }
}

class SymfonyProcessQueueTest extends TestCase
{
    public function testPushAndDispatchOneUsesFactory()
    {
        $created = [];
        $factory = function(array $cmd) use (&$created) {
            $created[] = $cmd;
            return new FakeProcess($cmd);
        };

        $queue = new SymfonyProcessQueue('php', 'bin/imagex', $factory);
        $queue->push(function() { return ['php', 'bin/imagex', 'generate', '123']; });

        $this->assertTrue($queue->dispatchOne());
        $this->assertCount(1, $created);
        $this->assertSame(['php', 'bin/imagex', 'generate', '123'], $created[0]);
    }

    public function testDispatchAllRunsAllJobs()
    {
        $created = [];
        $factory = function(array $cmd) use (&$created) {
            $created[] = $cmd;
            return new FakeProcess($cmd);
        };

        $queue = new SymfonyProcessQueue('php', 'bin/imagex', $factory);
        $queue->push(fn() => ['php','bin/imagex','a']);
        $queue->push(fn() => ['php','bin/imagex','b']);

        $this->assertSame(2, $queue->dispatchAll());
        $this->assertCount(2, $created);
    }
}
