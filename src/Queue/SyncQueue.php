<?php
namespace Piplup\ImageX\Queue;

use Piplup\ImageX\Contracts\JobQueueInterface;

class SyncQueue implements JobQueueInterface
{
    /** @var array<int,callable> */
    private array $jobs = [];

    public function push(callable $job): void
    {
        $this->jobs[] = $job;
    }

    public function dispatchOne(): bool
    {
        if (empty($this->jobs)) {
            return false;
        }
        $job = array_shift($this->jobs);
        $job();
        return true;
    }

    public function dispatchAll(): int
    {
        $count = 0;
        while ($this->dispatchOne()) {
            $count++;
        }
        return $count;
    }
}
