<?php
namespace Piplup\ImageX\Tests\Unit\Adapters;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Adapters\S3StorageAdapter;

class FakeS3Client
{
    public array $calls = [];

    public function putObject(array $args)
    {
        $this->calls[] = ['putObject', $args];
        return ['@metadata' => []];
    }

    public function headObject(array $args)
    {
        $this->calls[] = ['headObject', $args];
        return ['@metadata' => []];
    }

    public function getObject(array $args)
    {
        $this->calls[] = ['getObject', $args];
        return ['Body' => 'fake-body'];
    }

    public function deleteObject(array $args)
    {
        $this->calls[] = ['deleteObject', $args];
        return ['@metadata' => []];
    }
}

class S3StorageAdapterTest extends TestCase
{
    public function testPutExistsGetAndDeleteUseClient()
    {
        $client = new FakeS3Client();
        $adapter = new S3StorageAdapter('my-bucket', $client, 'us-east-1', 'prefix');

        $path = 'folder/file.txt';
        $contents = 'hello world';

        $stored = $adapter->put($path, $contents);
        $this->assertSame('prefix/folder/file.txt', $stored);

        $this->assertTrue($adapter->exists($path));
        $this->assertSame('fake-body', $adapter->get($path));
        $this->assertTrue($adapter->delete($path));

        $url = $adapter->url($path);
        $this->assertStringContainsString('https://my-bucket.s3.us-east-1.amazonaws.com/prefix/folder/file.txt', $url);

        $this->assertNotEmpty($client->calls);
    }
}
