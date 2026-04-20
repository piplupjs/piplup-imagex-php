<?php
namespace Piplup\ImageX\Tests\Unit\Watermark;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Watermark\Watermarker;

class FakeImagick
{
    public string $path;
    public int $width;
    public int $height;
    public $lastComposite = null;
    public $writtenTo = null;
    public $opacity = null;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->width = 800;
        $this->height = 600;
    }

    public function getImageWidth() { return $this->width; }
    public function getImageHeight() { return $this->height; }
    public function scaleImage($w, $h) { $this->width = $w; }
    public function setImageOpacity($o) { $this->opacity = $o; }
    public function compositeImage($other, $op, $x, $y) { $this->lastComposite = [$other, $op, $x, $y]; }
    public function writeImage($path) { $this->writtenTo = $path; }
}

class WatermarkerTest extends TestCase
{
    public function testApplyWithScaleOpacityAndPosition()
    {
        $targetPath = 'tests/fixtures/small.jpg';
        $watermarkPath = 'tests/fixtures/wm.png';

        $instances = [];
        $factory = function($path) use (&$instances) {
            $instances[$path] = new FakeImagick($path);
            return $instances[$path];
        };

        $watermarker = new Watermarker($factory);

        $opts = ['scale' => 10, 'opacity' => 50, 'position' => 'bottom-right', 'margin' => 5];
        $result = $watermarker->apply($targetPath, $watermarkPath, $opts);

        $this->assertTrue($result);
        $this->assertArrayHasKey($targetPath, $instances);
        $this->assertArrayHasKey($watermarkPath, $instances);

        $target = $instances[$targetPath];
        $wm = $instances[$watermarkPath];

        $this->assertSame($watermarkPath, $wm->path);
        $this->assertSame($targetPath, $target->writtenTo);
        $this->assertNotNull($target->lastComposite);
        $this->assertSame($wm, $target->lastComposite[0]);
    }

    public function testApplyDefaults()
    {
        $targetPath = 'a.jpg';
        $watermarkPath = 'b.png';
        $instances = [];
        $factory = function($path) use (&$instances) {
            $instances[$path] = new FakeImagick($path);
            return $instances[$path];
        };

        $watermarker = new Watermarker($factory);
        $this->assertTrue($watermarker->apply($targetPath, $watermarkPath));

        $this->assertArrayHasKey($targetPath, $instances);
        $this->assertNotNull($instances[$targetPath]->lastComposite);
    }
}
