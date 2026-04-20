<?php
namespace Piplup\ImageX\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Adapters\LocalStorageAdapter;
use Piplup\ImageX\Contracts\StorageAdapterInterface;

class StorageAdapterInterfaceTest extends TestCase
{
    public function testLocalStorageAdapterImplementsInterface()
    {
        $adapter = new LocalStorageAdapter(sys_get_temp_dir());
        $this->assertInstanceOf(StorageAdapterInterface::class, $adapter);
    }
}
