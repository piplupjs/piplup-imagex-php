<?php
namespace Piplup\ImageX\Adapters;

use Piplup\ImageX\Contracts\StorageAdapterInterface;

class S3StorageAdapter implements StorageAdapterInterface
{
    private string $bucket;
    private $client;
    private ?string $region;
    private string $prefix;
    private ?string $endpoint;

    public function __construct(string $bucket, $client = null, ?string $region = null, string $prefix = '', ?string $endpoint = null)
    {
        $this->bucket = $bucket;
        $this->client = $client;
        $this->region = $region;
        $this->prefix = trim($prefix, '/');
        $this->endpoint = $endpoint;
    }

    private function key(string $path): string
    {
        $path = ltrim($path, '/');
        return ($this->prefix !== '' ? $this->prefix . '/' : '') . $path;
    }

    public function put(string $path, string $contents): string
    {
        $key = $this->key($path);
        if ($this->client && method_exists($this->client, 'putObject')) {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $contents,
            ]);
            return $key;
        }

        throw new \RuntimeException('S3 client not available');
    }

    public function exists(string $path): bool
    {
        $key = $this->key($path);
        if ($this->client) {
            if (method_exists($this->client, 'doesObjectExist')) {
                return (bool)$this->client->doesObjectExist($this->bucket, $key);
            }

            try {
                $this->client->headObject(['Bucket' => $this->bucket, 'Key' => $key]);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function url(string $path): string
    {
        $key = $this->key($path);
        if ($this->endpoint) {
            return rtrim($this->endpoint, '/') . '/' . ltrim($key, '/');
        }

        if ($this->region) {
            return sprintf('https://%s.s3.%s.amazonaws.com/%s', $this->bucket, $this->region, $key);
        }

        return sprintf('https://%s.s3.amazonaws.com/%s', $this->bucket, $key);
    }

    public function get(string $path): string
    {
        $key = $this->key($path);
        if ($this->client && method_exists($this->client, 'getObject')) {
            $res = $this->client->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
            if (is_array($res) && isset($res['Body'])) {
                return (string)$res['Body'];
            }
            if (is_object($res) && method_exists($res, 'get')) {
                return (string)$res->get('Body');
            }
        }

        throw new \RuntimeException('S3 client not available');
    }

    public function delete(string $path): bool
    {
        $key = $this->key($path);
        if ($this->client && method_exists($this->client, 'deleteObject')) {
            $this->client->deleteObject(['Bucket' => $this->bucket, 'Key' => $key]);
            return true;
        }

        return false;
    }
}
