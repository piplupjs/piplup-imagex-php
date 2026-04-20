<?php
namespace Piplup\ImageX\Tests\Unit\Resizer;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Resizer\SimpleResizer;

class SimpleResizerTest extends TestCase
{
    public function testResizeCreatesTarget()
    {
        $source = __DIR__ . '/../../fixtures/small.jpg';
        $tmpDir = sys_get_temp_dir() . '/compressx_resizer_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $target = $tmpDir . '/small_resized.jpg';

        $resizer = new SimpleResizer();
        $resizer->resize($source, $target, 200, null, ['quality' => 80]);

        $this->assertFileExists($target);
        $this->assertGreaterThan(0, filesize($target));

        @unlink($target);
        @rmdir($tmpDir);
    }
}
