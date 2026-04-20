<?php
namespace Piplup\ImageX\Tests\Unit\Converter;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Converters\ImagickConverter;

class ImagickConverterTest extends TestCase
{
    public function testConvertCreatesTarget()
    {
        $source = __DIR__ . '/../../fixtures/small.jpg';
        $tmpDir = sys_get_temp_dir() . '/compressx_imagick_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $target = $tmpDir . '/small_converted.jpg';

        $conv = new ImagickConverter();
        $conv->convert($source, $target, ['format' => 'jpeg', 'quality' => 75]);

        $this->assertFileExists($target);
        $this->assertGreaterThan(0, filesize($target));

        @unlink($target);
        @rmdir($tmpDir);
    }
}
