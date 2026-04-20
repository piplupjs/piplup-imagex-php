<?php
namespace Piplup\ImageX\Tests\Unit\Converter;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Converters\NullConverter;

class NullConverterTest extends TestCase
{
    public function testConvertCopiesFile()
    {
        $source = __DIR__ . '/../../fixtures/small.jpg';
        $tmpDir = sys_get_temp_dir() . '/compressx_null_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $target = $tmpDir . '/small_copy.jpg';

        $conv = new NullConverter();
        $conv->convert($source, $target);

        $this->assertFileExists($target);
        $this->assertGreaterThan(0, filesize($target));

        @unlink($target);
        @rmdir($tmpDir);
    }
}
