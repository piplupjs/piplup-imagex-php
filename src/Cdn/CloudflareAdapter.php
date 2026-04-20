<?php
namespace Piplup\ImageX\Cdn;

class CloudflareAdapter
{
    private string $zoneId;
    private ?string $apiKey;
    private $client;

    public function __construct(string $zoneId, ?string $apiKey = null, $client = null)
    {
        $this->zoneId = $zoneId;
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    public function purge(string $url): bool
    {
        return $this->purgeMany([$url]);
    }

    public function purgeMany(array $urls): bool
    {
        $endpoint = sprintf('https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $this->zoneId);
        $payload = ['files' => array_values($urls)];

        if ($this->client && method_exists($this->client, 'request')) {
            $options = [
                'headers' => [
                    'Authorization' => $this->apiKey ? 'Bearer ' . $this->apiKey : '',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload),
            ];

            $res = $this->client->request('POST', $endpoint, $options);

            if (is_object($res) && method_exists($res, 'getBody')) {
                $body = (string) $res->getBody();
                $data = json_decode($body, true);
                return isset($data['success']) ? (bool)$data['success'] : false;
            }

            if (is_array($res) && isset($res['body'])) {
                $data = is_string($res['body']) ? json_decode($res['body'], true) : $res['body'];
                return isset($data['success']) ? (bool)$data['success'] : false;
            }

            return false;
        }

        if ($this->client && method_exists($this->client, 'post')) {
            $res = $this->client->post($endpoint, ['json' => $payload, 'headers' => ['Authorization' => $this->apiKey ? 'Bearer ' . $this->apiKey : '']]);
            if (is_array($res) && isset($res['success'])) {
                return (bool)$res['success'];
            }
            if (is_object($res) && method_exists($res, 'getBody')) {
                $body = (string) $res->getBody();
                $data = json_decode($body, true);
                return isset($data['success']) ? (bool)$data['success'] : false;
            }
            return false;
        }

        throw new \RuntimeException('HTTP client not available');
    }
}
