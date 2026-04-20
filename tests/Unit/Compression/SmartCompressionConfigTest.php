<?php
namespace Piplup\ImageX\Tests\Unit\Compression;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Compression\CompressionProfile;
use Piplup\ImageX\Compression\SmartCompression;
use Piplup\ImageX\Config\Config;

class SmartCompressionConfigTest extends TestCase
{
    public function testRecommendUsesConfigDefaults()
    {
        $profile = new CompressionProfile([
            'webp' => [],
        ]);

        $config = new Config(42, false, true);
        $smart = new SmartCompression($profile, $config);

        $metadata = [
            'mime' => 'image/jpeg',
            'size' => 200,
        ];

        $result = $smart->recommend($metadata);

        $this->assertCount(1, $result['conversions']);
        $this->assertEquals(42, $result['conversions'][0]['quality']);
        $this->assertFalse($result['keep_original']);
        $this->assertTrue($result['remove_if_larger']);
    }
}
