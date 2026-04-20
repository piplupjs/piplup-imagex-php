<?php
namespace Piplup\ImageX\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\ImageManager;

class AutoloadTest extends TestCase
{
    public function testAutoloadsImageManager()
    {
        $manager = new ImageManager();
        $this->assertInstanceOf(ImageManager::class, $manager);
    }
}
