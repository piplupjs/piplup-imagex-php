<?php
namespace Piplup\ImageX\Tests\Unit\Console;

use PHPUnit\Framework\TestCase;

class BinCompressxRegenerateTest extends TestCase
{
    public function testRegenerateOutputsJson()
    {
        $cmd = 'php ' . escapeshellarg('bin/compressx') . ' regenerate tests/fixtures/small.jpg --force';
        exec($cmd, $out, $code);

        $this->assertSame(0, $code);
        $json = implode("\n", $out);
        $data = json_decode($json, true);
        $this->assertSame('regenerate', $data['command']);
        $this->assertStringContainsString('small.jpg', $data['path']);
        $this->assertTrue($data['options']['force']);
    }
}
