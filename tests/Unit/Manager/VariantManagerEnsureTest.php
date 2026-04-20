<?php
namespace Piplup\ImageX\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Manager\VariantManager;
use Piplup\ImageX\Generators\ResponsiveGenerator;
use Piplup\ImageX\Contracts\ResizerInterface;
use Piplup\ImageX\Contracts\StorageAdapterInterface;
use Piplup\ImageX\Contracts\ConverterInterface;

class VariantManagerEnsureTest extends TestCase
{
    public function testEnsureVariantsCreatesMissing()
    {
        $fixture = __DIR__ . '/../../fixtures/small.jpg';

        $generator = $this->createMock(ResponsiveGenerator::class);
        $generator->method('computeWidths')->willReturn([320, 640]);

        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->expects($this->exactly(2))->method('resize')
            ->willReturnCallback(function ($src, $target) {
                // create a small placeholder file so file_get_contents works
                file_put_contents($target, 'x');
            });

        $storage = $this->createMock(StorageAdapterInterface::class);
        $storage->method('exists')->willReturn(false);
        $storage->expects($this->exactly(2))->method('put');
        $storage->method('url')->willReturnCallback(function ($p) {
            return 'https://cdn.example/' . ltrim($p, '/');
        });

        $converter = $this->createMock(ConverterInterface::class);
        $converter->expects($this->exactly(2))->method('convert')
            ->willReturnCallback(function ($src, $target) {
                file_put_contents($target, 'c');
            });

        $manager = new VariantManager($resizer, $storage, $converter, $generator);
        $variants = $manager->ensureVariants($fixture, ['storage_path' => 'test-uploads', 'format' => 'jpg']);

        $this->assertCount(2, $variants);
        foreach ($variants as $v) {
            $this->assertArrayHasKey('url', $v);
            $this->assertStringStartsWith('https://cdn.example/', $v['url']);
            $this->assertArrayHasKey('created', $v);
            $this->assertTrue($v['created']);
        }
    }

    public function testEnsureVariantsSkipsExisting()
    {
        $fixture = __DIR__ . '/../../fixtures/small.jpg';

        $generator = $this->createMock(ResponsiveGenerator::class);
        $generator->method('computeWidths')->willReturn([320, 640]);

        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->expects($this->once())->method('resize')
            ->willReturnCallback(function ($src, $target) {
                file_put_contents($target, 'x');
            });

        $storage = $this->createMock(StorageAdapterInterface::class);
        // simulate that 320 exists but 640 does not
        $storage->method('exists')->willReturnCallback(function ($p) {
            return strpos($p, '-320w.') !== false;
        });
        $storage->expects($this->once())->method('put');
        $storage->method('url')->willReturnCallback(function ($p) {
            return 'https://cdn.example/' . ltrim($p, '/');
        });

        $manager = new VariantManager($resizer, $storage, null, $generator);
        $variants = $manager->ensureVariants($fixture, ['storage_path' => 'test-uploads', 'format' => 'jpg']);

        $this->assertCount(2, $variants);
        $created = array_filter($variants, function ($v) { return !empty($v['created']); });
        $this->assertCount(1, $created);
    }
}
