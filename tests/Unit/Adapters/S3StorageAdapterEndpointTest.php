<?php
namespace Piplup\ImageX\Tests\Unit\Adapters;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Adapters\S3StorageAdapter;

class S3StorageAdapterEndpointTest extends TestCase
{
    public function testUrlUsesEndpointIfProvided()
    {
        $adapter = new S3StorageAdapter('my-bucket', null, null, 'prefix', 'https://cdn.example.com/');
        $this->assertSame('https://cdn.example.com/prefix/f/file.jpg', $adapter->url('f/file.jpg'));

        $adapter2 = new S3StorageAdapter('my-bucket', null, null, '', 'https://cdn.example.com');
        $this->assertSame('https://cdn.example.com/file.jpg', $adapter2->url('/file.jpg'));
    }

    public function testUrlWithoutRegionUsesS3Hostname()
    {
        $adapter = new S3StorageAdapter('bucket', null, null, 'pre');
        $this->assertSame('https://bucket.s3.amazonaws.com/pre/path.jpg', $adapter->url('path.jpg'));
    }
}
