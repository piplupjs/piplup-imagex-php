<?php
namespace Piplup\ImageX\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Manager\VariantManager;
use Piplup\ImageX\Resizer\SimpleResizer;
use Piplup\ImageX\Adapters\LocalStorageAdapter;
use Piplup\ImageX\Converters\NullConverter;

class VariantGenerationIntegrationTest extends TestCase
{
    public function testGeneratesAndStoresVariants()
    {
        $fixture = __DIR__ . '/../fixtures/small.jpg';
        $storageBase = sys_get_temp_dir() . '/imagex_integration_' . uniqid();
        $storage = new LocalStorageAdapter($storageBase, null);
        $resizer = new SimpleResizer();
        $converter = new NullConverter();
        $manager = new VariantManager($resizer, $storage, $converter);

        $variants = $manager->generateVariants($fixture, ['widths' => [320, 640], 'storage_path' => 'test-uploads']);
        $this->assertCount(2, $variants);

        foreach ($variants as $variant) {
            $this->assertArrayHasKey('url', $variant);
            $this->assertArrayHasKey('path', $variant);
            $this->assertTrue($storage->exists($variant['path']), 'Stored file does not exist: ' . $variant['path']);
        }

        // Cleanup
        foreach ($variants as $variant) {
            $storage->delete($variant['path']);
        }
    }
}
