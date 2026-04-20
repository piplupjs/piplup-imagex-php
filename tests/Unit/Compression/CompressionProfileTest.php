<?php
namespace Piplup\ImageX\Tests\Unit\Compression;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Compression\CompressionProfile;

class CompressionProfileTest extends TestCase
{
    public function testProfileStoresFormats()
    {
        $profile = new CompressionProfile([
            'webp' => ['quality' => 80],
            'avif' => ['quality' => 50, 'min_size' => 100],
        ]);

        $this->assertSame(['webp', 'avif'], $profile->getFormats());
        $this->assertSame(80, $profile->getQuality('webp'));
        $this->assertSame(50, $profile->getQuality('avif'));
        $this->assertSame(100, $profile->getMinSize('avif'));
    }
}
