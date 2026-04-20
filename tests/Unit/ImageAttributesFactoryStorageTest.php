<?php
namespace Piplup\ImageX\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\ImageAttributesFactory;
use Piplup\ImageX\Contracts\StorageAdapterInterface;

class ImageAttributesFactoryStorageTest extends TestCase
{
    public function testSrcsetUsesStorageAdapterUrls()
    {
        $factory = new ImageAttributesFactory();
        $path = __DIR__ . '/../fixtures/small.jpg';
        $this->assertFileExists($path);

        $generated = [
            320 => 'test-uploads/image-320.jpg',
            640 => 'test-uploads/image-640.jpg',
        ];

        $storage = $this->createMock(StorageAdapterInterface::class);
        $storage->method('url')->willReturnCallback(function ($p) {
            return 'https://cdn.example/' . ltrim($p, '/');
        });

        $attrs = $factory->getAttributes($path, ['generated_variants' => $generated, 'storage_adapter' => $storage]);

        $this->assertStringContainsString('https://cdn.example/test-uploads/image-320.jpg 320w', $attrs['srcset']);
        $this->assertStringContainsString('https://cdn.example/test-uploads/image-640.jpg 640w', $attrs['srcset']);
    }
}
