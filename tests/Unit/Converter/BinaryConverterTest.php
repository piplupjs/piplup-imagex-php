<?php
namespace Piplup\ImageX\Tests\Unit\Converter;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Converters\BinaryConverter;

class BinaryConverterTest extends TestCase
{
    public function testConvertCreatesTarget()
    {
        $source = __DIR__ . '/../../fixtures/small.jpg';
        $tmpDir = sys_get_temp_dir() . '/compressx_binary_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $target = $tmpDir . '/small_converted.webp';

        $conv = new BinaryConverter();
        $conv->convert($source, $target, ['format' => 'webp', 'quality' => 75]);

        $this->assertFileExists($target);
        $this->assertGreaterThan(0, filesize($target));

        @unlink($target);
        @rmdir($tmpDir);
    }
}
