<?php
namespace Piplup\ImageX\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Config\Config;

class ConfigTest extends TestCase
{
    public function testDefaultsAndGetters()
    {
        $config = new Config(60, false, true);

        $this->assertSame(60, $config->getDefaultQuality());
        $this->assertFalse($config->getKeepOriginals());
        $this->assertTrue($config->getRemoveIfLarger());
    }
}
