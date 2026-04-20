<?php
namespace Piplup\ImageX\Compression;

final class CompressionProfile
{
    private array $formats;

    public function __construct(array $formats = [])
    {
        $this->formats = $formats;
    }

    public static function fromArray(array $arr): self
    {
        return new self($arr);
    }

    public function getFormats(): array
    {
        return array_keys($this->formats);
    }

    public function getFormatOptions(string $format): array
    {
        return $this->formats[$format] ?? [];
    }

    public function getQuality(string $format, int $default = 75): int
    {
        $opts = $this->getFormatOptions($format);
        return isset($opts['quality']) ? (int)$opts['quality'] : $default;
    }

    public function getMinSize(string $format): int
    {
        $opts = $this->getFormatOptions($format);
        return isset($opts['min_size']) ? (int)$opts['min_size'] : 0;
    }
}
