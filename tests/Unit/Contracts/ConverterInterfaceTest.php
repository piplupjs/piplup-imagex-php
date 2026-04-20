<?php
namespace Piplup\ImageX\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Converters\NullConverter;
use Piplup\ImageX\Converters\ImagickConverter;
use Piplup\ImageX\Contracts\ConverterInterface;

class ConverterInterfaceTest extends TestCase
{
    public function testConvertersImplementInterface()
    {
        $null = new NullConverter();
        $imagick = new ImagickConverter();

        $this->assertInstanceOf(ConverterInterface::class, $null);
        $this->assertInstanceOf(ConverterInterface::class, $imagick);
    }
}
