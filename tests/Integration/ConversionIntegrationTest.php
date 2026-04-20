<?php
namespace Piplup\ImageX\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Manager\VariantManager;
use Piplup\ImageX\Resizer\SimpleResizer;
use Piplup\ImageX\Adapters\LocalStorageAdapter;
use Piplup\ImageX\Converters\BinaryConverter;
use Piplup\ImageX\Compression\CompressionProfile;
use Piplup\ImageX\Compression\SmartCompression;

class ConversionIntegrationTest extends TestCase
{
    public function testProfileRecommendationsAreAppliedAndStored()
    {
        $fixture = __DIR__ . '/../fixtures/small.jpg';
        $storageBase = sys_get_temp_dir() . '/imagex_convint_' . uniqid();
        $storage = new LocalStorageAdapter($storageBase, null);
        $resizer = new SimpleResizer();
        $converter = new BinaryConverter();
        $manager = new VariantManager($resizer, $storage, $converter);

        $profile = new CompressionProfile([
            'webp' => ['quality' => 55],
            'avif' => ['quality' => 45],
        ]);

        $smart = new SmartCompression($profile);

        $metadata = [
            'mime' => (string)@getimagesize($fixture)['mime'] ?: 'image/jpeg',
            'size' => filesize($fixture),
        ];

        $recommend = $smart->recommend($metadata);

        $this->assertArrayHasKey('conversions', $recommend);
        $this->assertNotEmpty($recommend['conversions']);

        foreach ($recommend['conversions'] as $conv) {
            $variants = $manager->ensureVariants($fixture, ['storage_path' => 'test-uploads', 'widths' => [320], 'format' => $conv['format'], 'quality' => $conv['quality']]);
            $this->assertGreaterThanOrEqual(1, count($variants));

            $found = false;
            foreach ($variants as $v) {
                if (str_ends_with($v['path'], '.' . $conv['format'])) {
                    $found = true;
                    $this->assertTrue($storage->exists($v['path']));
                    $storage->delete($v['path']);
                }
            }
            $this->assertTrue($found, 'Expected at least one .' . $conv['format'] . ' variant');
        }
    }
}
