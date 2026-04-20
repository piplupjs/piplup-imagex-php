<?php
namespace Piplup\ImageX\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\ImageManager;
use Piplup\ImageX\Contracts\ImageManagerInterface;

class ImageManagerInterfaceTest extends TestCase
{
    public function testImageManagerImplementsInterfaceAndReturnsAttributes()
    {
        $manager = new ImageManager();
        $this->assertInstanceOf(ImageManagerInterface::class, $manager);

        $path = __DIR__ . '/../../fixtures/small.jpg';
        $attrs = $manager->getAttributes($path);
        $this->assertArrayHasKey('src', $attrs);
        $this->assertSame($path, $attrs['src']);
    }
}
