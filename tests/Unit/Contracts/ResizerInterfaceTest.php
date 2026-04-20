<?php
namespace Piplup\ImageX\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Resizer\SimpleResizer;
use Piplup\ImageX\Contracts\ResizerInterface;

class ResizerInterfaceTest extends TestCase
{
    public function testSimpleResizerImplementsInterface()
    {
        $resizer = new SimpleResizer();
        $this->assertInstanceOf(ResizerInterface::class, $resizer);
    }
}
