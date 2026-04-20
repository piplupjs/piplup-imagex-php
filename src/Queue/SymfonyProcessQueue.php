<?php
namespace Piplup\ImageX\Queue;

use Piplup\ImageX\Contracts\JobQueueInterface;

class SymfonyProcessQueue implements JobQueueInterface
{
    private string $phpBinary;
    private string $binPath;
    /** @var callable */
    private $processFactory;
    /** @var array<int,callable> */
    private array $jobs = [];

    public function __construct(string $phpBinary = 'php', string $binPath = 'bin/imagex', ?callable $processFactory = null)
    {
        $this->phpBinary = $phpBinary;
        $this->binPath = $binPath;
        $this->processFactory = $processFactory ?: function(array $cmd) {
            if (!class_exists(\Symfony\Component\Process\Process::class)) {
                throw new \RuntimeException('Symfony Process component not installed');
            }
            return new \Symfony\Component\Process\Process($cmd);
        };
    }

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
        $cmdOrArgs = $job();

        if (is_string($cmdOrArgs)) {
            $cmd = preg_split('/\s+/', trim($cmdOrArgs));
        } elseif (is_array($cmdOrArgs)) {
            $cmd = $cmdOrArgs;
        } else {
            throw new \InvalidArgumentException('Job must return a command string or array');
        }

        $process = call_user_func($this->processFactory, $cmd);
        if (!is_object($process) || !method_exists($process, 'start')) {
            throw new \RuntimeException('Process factory must return object with start() method');
        }

        $process->start();
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
