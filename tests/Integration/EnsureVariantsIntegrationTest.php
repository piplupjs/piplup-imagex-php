<?php
namespace Piplup\ImageX\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Manager\VariantManager;
use Piplup\ImageX\Resizer\SimpleResizer;
use Piplup\ImageX\Adapters\LocalStorageAdapter;
use Piplup\ImageX\Converters\NullConverter;

class EnsureVariantsIntegrationTest extends TestCase
{
    public function testEnsureVariantsCreatesAndSkipsOnSecondRun()
    {
        $fixture = __DIR__ . '/../fixtures/small.jpg';
        $storageBase = sys_get_temp_dir() . '/imagex_ensure_' . uniqid();
        $storage = new LocalStorageAdapter($storageBase, null);
        $resizer = new SimpleResizer();
        $converter = new NullConverter();
        $manager = new VariantManager($resizer, $storage, $converter);

        $opts = ['storage_path' => 'test-uploads', 'widths' => [320, 640], 'format' => 'jpg'];

        $variants1 = $manager->ensureVariants($fixture, $opts);
        $this->assertCount(2, $variants1);

        foreach ($variants1 as $v) {
            $this->assertTrue($storage->exists($v['path']), 'Expected file to exist: ' . $v['path']);
        }

        // Capture mtimes
        $mtimes = [];
        foreach ($variants1 as $v) {
            $mtimes[] = filemtime($storageBase . DIRECTORY_SEPARATOR . ltrim($v['path'], '/'));
        }

        // Run again - should skip existing files
        sleep(1);
        $variants2 = $manager->ensureVariants($fixture, $opts);
        $this->assertCount(2, $variants2);

        // Ensure mtimes unchanged (skipped)
        $i = 0;
        foreach ($variants2 as $v) {
            $this->assertEquals($mtimes[$i], filemtime($storageBase . DIRECTORY_SEPARATOR . ltrim($v['path'], '/')));
            $i++;
            // cleanup
            $storage->delete($v['path']);
        }
    }
}
