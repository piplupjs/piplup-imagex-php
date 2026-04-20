<?php
namespace Piplup\ImageX\Contracts;

interface JobQueueInterface
{
    /**
     * Push a job to the queue.
     *
     * @param callable $job
     */
    public function push(callable $job): void;

    /**
     * Dispatch a single job from the queue.
     *
     * @return bool True if a job was dispatched.
     */
    public function dispatchOne(): bool;

    /**
     * Dispatch all jobs in the queue.
     *
     * @return int Number of jobs dispatched.
     */
    public function dispatchAll(): int;
}
