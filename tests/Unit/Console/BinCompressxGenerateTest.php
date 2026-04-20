<?php
namespace Piplup\ImageX\Tests\Unit\Console;

use PHPUnit\Framework\TestCase;

class BinCompressxGenerateTest extends TestCase
{
    public function testGenerateOutputsJson()
    {
        $cmd = 'php ' . escapeshellarg('bin/compressx') . ' generate tests/fixtures/small.jpg --widths=320,640 --format=webp';
        exec($cmd, $out, $code);

        $this->assertSame(0, $code, "Exit code should be 0, output: " . implode("\n", $out));
        $json = implode("\n", $out);
        $data = json_decode($json, true);
        $this->assertSame('generate', $data['command']);
        $this->assertStringContainsString('small.jpg', $data['path']);
        $this->assertSame('320,640', $data['options']['widths']);
        $this->assertSame('webp', $data['options']['format']);
    }
}
