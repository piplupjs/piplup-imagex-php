<?php
namespace Piplup\ImageX\Tests\Unit\Cdn;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Cdn\CloudflareAdapter;

class FakeClient
{
    public array $calls = [];
    public string $responseBody;

    public function __construct($responseBody = null)
    {
        $this->responseBody = $responseBody ?: json_encode(['success' => true]);
    }

    public function request($method, $uri, $options = [])
    {
        $this->calls[] = ['method' => $method, 'uri' => $uri, 'options' => $options];
        $body = $this->responseBody;
        return new class($body) {
            private $body;
            public function __construct($body) { $this->body = $body; }
            public function getBody() { return $this->body; }
            public function getStatusCode() { return 200; }
        };
    }
}

class CloudflareAdapterTest extends TestCase
{
    public function testPurgeManyCallsClientAndReturnsTrue()
    {
        $client = new FakeClient();
        $zone = 'zone-42';
        $key = 'api-key';
        $adapter = new CloudflareAdapter($zone, $key, $client);

        $urls = ['https://example.com/image-320w.webp'];
        $result = $adapter->purgeMany($urls);

        $this->assertTrue($result);
        $this->assertNotEmpty($client->calls);
        $call = $client->calls[0];
        $this->assertSame('POST', $call['method']);
        $this->assertStringContainsString($zone, $call['uri']);
        $this->assertArrayHasKey('body', $call['options']);
        $this->assertStringContainsString('image-320w.webp', $call['options']['body']);
    }

    public function testPurgeSingleUrl()
    {
        $client = new FakeClient();
        $adapter = new CloudflareAdapter('z', 'k', $client);
        $this->assertTrue($adapter->purge('https://example.com/a.jpg'));
    }
}
