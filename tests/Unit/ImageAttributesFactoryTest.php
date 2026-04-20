<?php
namespace Piplup\ImageX\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\ImageAttributesFactory;

class ImageAttributesFactoryTest extends TestCase
{
    public function testGetAttributesReturnsExpectedKeys()
    {
        $factory = new ImageAttributesFactory();
        $path = __DIR__ . '/../fixtures/small.jpg';
        $this->assertFileExists($path);

        $attrs = $factory->getAttributes($path);

        $this->assertArrayHasKey('src', $attrs);
        $this->assertArrayHasKey('srcset', $attrs);
        $this->assertArrayHasKey('sizes', $attrs);
        $this->assertArrayHasKey('width', $attrs);
        $this->assertArrayHasKey('height', $attrs);
        $this->assertArrayHasKey('type', $attrs);

        $this->assertSame($path, $attrs['src']);
        $this->assertIsString($attrs['srcset']);
        $this->assertMatchesRegularExpression('/\d+w/', $attrs['srcset']);
        $this->assertGreaterThan(0, $attrs['width']);
        $this->assertGreaterThan(0, $attrs['height']);
    }
}
