<?php
namespace Piplup\ImageX\Tests\Unit\Adapters;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Adapters\LocalStorageAdapter;

class LocalStorageAdapterNoBaseUrlTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/compressx_local_test_' . uniqid();
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir)) {
            $this->rrmdir($this->dir);
        }
    }

    private function rrmdir(string $dir): void
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    public function testUrlReturnsFilesystemPathWhenNoBaseUrl()
    {
        $adapter = new LocalStorageAdapter($this->dir, null);

        $path = 'variants/test.txt';
        $contents = 'hello fs';
        $stored = $adapter->put($path, $contents);

        $this->assertFileExists($stored);
        $this->assertTrue($adapter->exists($path));
        $this->assertSame($contents, $adapter->get($path));

        $url = $adapter->url($path);
        $this->assertStringContainsString($this->dir, $url);
        $this->assertStringEndsWith('variants/test.txt', str_replace('\\', '/', $url));
    }
}
