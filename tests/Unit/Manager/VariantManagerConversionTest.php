<?php
namespace Piplup\ImageX\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Manager\VariantManager;
use Piplup\ImageX\Adapters\LocalStorageAdapter;
use Piplup\ImageX\Contracts\ResizerInterface;
use Piplup\ImageX\Contracts\ConverterInterface;

class VariantManagerConversionTest extends TestCase
{
    public function testEnsureVariantsUsesConverterAndStoresConvertedFormat()
    {
        $fixture = __DIR__ . '/../../fixtures/small.jpg';
        $storageBase = sys_get_temp_dir() . '/imagex_vmconv_' . uniqid();
        $storage = new LocalStorageAdapter($storageBase, null);

        $resizer = new class implements ResizerInterface {
            public function resize(string $sourcePath, string $targetPath, int $width, ?int $height = null, array $options = []): void
            {
                if (!is_dir(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0777, true);
                }
                copy($sourcePath, $targetPath);
            }
        };

        $converter = new class implements ConverterInterface {
            public function convert(string $sourcePath, string $targetPath, array $options = []): void
            {
                if (!is_dir(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0777, true);
                }
                copy($sourcePath, $targetPath);
            }
        };

        $manager = new VariantManager($resizer, $storage, $converter);

        $variants = $manager->ensureVariants($fixture, ['storage_path' => 'test-uploads', 'widths' => [320], 'format' => 'webp', 'force' => true]);

        $this->assertGreaterThanOrEqual(1, count($variants));

        $found = false;
        foreach ($variants as $variant) {
            if (str_ends_with($variant['path'], '.webp')) {
                $found = true;
                $this->assertTrue($storage->exists($variant['path']));
                // cleanup
                $storage->delete($variant['path']);
            }
        }

        $this->assertTrue($found, 'Expected at least one .webp variant to be generated');
    }
}
