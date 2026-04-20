<?php
namespace Piplup\ImageX\Tests\Unit\Url;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Url\CdnUrlGenerator;

class CdnUrlGeneratorTest extends TestCase
{
    public function testUsesCdnWhenProvided()
    {
        $g = new CdnUrlGenerator();
        $this->assertSame('https://cdn.example.com/f/file.jpg', $g->generate('f/file.jpg', ['cdn' => 'https://cdn.example.com']));
    }

    public function testFallsBackToBaseOrRelative()
    {
        $g = new CdnUrlGenerator();
        $this->assertSame('https://example.com/f/file.jpg', $g->generate('f/file.jpg', ['base' => 'https://example.com']));
        $this->assertSame('f/file.jpg', $g->generate('f/file.jpg'));
    }
}
