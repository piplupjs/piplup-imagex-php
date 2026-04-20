<?php
namespace Piplup\ImageX\Tests\Integration\Console;

use PHPUnit\Framework\TestCase;

class FunctionalCliTest extends TestCase
{
    public function testGenerateUsesImageManagerWhenEnvSet()
    {
        $bin = escapeshellarg('bin/compressx');
        $img = escapeshellarg('tests/fixtures/small.jpg');
        $env = "IMAGE_MANAGER_CLASS='Piplup\\ImageX\\ImageManager'";

        $cmd = "$env php $bin generate $img --widths=320,640";
        exec($cmd, $out, $code);

        $this->assertSame(0, $code, 'generate command should exit 0');
        $json = implode("\n", $out);
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertSame('generate', $data['command']);
        $this->assertArrayHasKey('attributes', $data);
        $this->assertSame('tests/fixtures/small.jpg', $data['attributes']['src']);
        $this->assertArrayHasKey('width', $data['attributes']);
    }

    public function testConvertAndRegenerateReturnExpectedJson()
    {
        $bin = escapeshellarg('bin/compressx');
        $img = escapeshellarg('tests/fixtures/small.jpg');

        $cmd = "php $bin convert $img --format=webp";
        exec($cmd, $out, $code);
        $this->assertSame(0, $code);
        $data = json_decode(implode("\n", $out), true);
        $this->assertSame('convert', $data['command']);
        $this->assertSame('webp', $data['options']['format']);

        $cmd2 = "php $bin regenerate $img --force";
        exec($cmd2, $out2, $code2);
        $this->assertSame(0, $code2);
        $data2 = json_decode(implode("\n", $out2), true);
        $this->assertSame('regenerate', $data2['command']);
        $this->assertTrue($data2['options']['force']);
    }
}
