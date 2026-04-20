<?php
namespace Piplup\ImageX\Tests\Unit\Url;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Url\RelativeUrlGenerator;

class RelativeUrlGeneratorTest extends TestCase
{
    public function testGeneratesRelativePathWithoutLeadingSlash()
    {
        $g = new RelativeUrlGenerator();
        $this->assertSame('folder/file.jpg', $g->generate('/folder/file.jpg'));
        $this->assertSame('a/b.png', $g->generate('a/b.png'));
    }
}
