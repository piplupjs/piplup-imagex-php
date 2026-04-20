<?php
namespace Piplup\ImageX\Tests\Unit\Console;

use PHPUnit\Framework\TestCase;

class BinCompressxConvertTest extends TestCase
{
    public function testConvertOutputsJson()
    {
        $cmd = 'php ' . escapeshellarg('bin/compressx') . ' convert tests/fixtures/small.jpg --format=avif';
        exec($cmd, $out, $code);

        $this->assertSame(0, $code);
        $json = implode("\n", $out);
        $data = json_decode($json, true);
        $this->assertSame('convert', $data['command']);
        $this->assertStringContainsString('small.jpg', $data['path']);
        $this->assertSame('avif', $data['options']['format']);
    }
}
