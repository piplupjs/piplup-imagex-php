<?php
namespace Piplup\ImageX\Tests\Unit\Url;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Url\AbsoluteUrlGenerator;

class AbsoluteUrlGeneratorTest extends TestCase
{
    public function testGeneratesAbsoluteUrlWithBase()
    {
        $g = new AbsoluteUrlGenerator();
        $this->assertSame('https://example.com/path/file.jpg', $g->generate('path/file.jpg', ['base' => 'https://example.com']));
        $this->assertSame('https://example.com/path/file.jpg', $g->generate('/path/file.jpg', ['base' => 'https://example.com/']));
    }

    public function testThrowsIfNoBase()
    {
        $this->expectException(\InvalidArgumentException::class);
        $g = new AbsoluteUrlGenerator();
        $g->generate('a.jpg');
    }
}
