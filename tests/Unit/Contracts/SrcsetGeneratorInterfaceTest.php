<?php
namespace Piplup\ImageX\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Contracts\SrcsetGeneratorInterface;

class SrcsetGeneratorInterfaceTest extends TestCase
{
    public function testInterfaceExists()
    {
        $this->assertTrue(interface_exists(SrcsetGeneratorInterface::class));
    }
}
