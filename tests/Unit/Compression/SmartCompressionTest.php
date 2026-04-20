<?php
namespace Piplup\ImageX\Tests\Unit\Compression;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Compression\CompressionProfile;
use Piplup\ImageX\Compression\SmartCompression;

class SmartCompressionTest extends TestCase
{
    public function testRecommendIncludesFormatsBasedOnSizeAndMime()
    {
        $profile = new CompressionProfile([
            'webp' => ['quality' => 80, 'min_size' => 0],
            'avif' => ['quality' => 50, 'min_size' => 100],
        ]);
        $smart = new SmartCompression($profile);

        $metadata = [
            'mime' => 'image/jpeg',
            'size' => 200,
        ];

        $result = $smart->recommend($metadata);

        $this->assertArrayHasKey('conversions', $result);
        $this->assertCount(2, $result['conversions']);
        $this->assertEquals('webp', $result['conversions'][0]['format']);
        $this->assertEquals(80, $result['conversions'][0]['quality']);
        $this->assertEquals('avif', $result['conversions'][1]['format']);
        $this->assertEquals(50, $result['conversions'][1]['quality']);
        $this->assertTrue($result['keep_original']);
        $this->assertFalse($result['remove_if_larger']);
    }

    public function testRecommendSkipsSameFormat()
    {
        $profile = new CompressionProfile([
            'webp' => ['quality' => 80],
        ]);
        $smart = new SmartCompression($profile);

        $metadata = [
            'mime' => 'image/webp',
            'size' => 1000,
        ];

        $result = $smart->recommend($metadata);

        $this->assertCount(0, $result['conversions']);
    }
}
