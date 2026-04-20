<?php
namespace Piplup\ImageX\Compression;

use Piplup\ImageX\Config\Config;

class SmartCompression
{
    private CompressionProfile $profile;
    private ?Config $config;

    public function __construct(CompressionProfile $profile, ?Config $config = null)
    {
        $this->profile = $profile;
        $this->config = $config;
    }

    /**
     * Recommend conversions based on metadata.
     * Return structure: ['conversions' => [['format'=>'webp','quality'=>80],...], 'keep_original' => bool, 'remove_if_larger' => bool]
     */
    public function recommend(array $metadata, array $options = []): array
    {
        $mime = strtolower($metadata['mime'] ?? '');
        $size = isset($metadata['size']) ? (int)$metadata['size'] : 0;

        $recommendations = [];
        foreach ($this->profile->getFormats() as $format) {
            if ($this->matchesFormat($mime, $format)) {
                continue;
            }
            $minSize = $this->profile->getMinSize($format);
            if ($size < $minSize) {
                continue;
            }
            $defaultQuality = $this->config ? $this->config->getDefaultQuality() : 75;
            $recommendations[] = [
                'format' => $format,
                'quality' => $this->profile->getQuality($format, $defaultQuality),
            ];
        }

        $keepOriginal = isset($options['keep_originals'])
            ? (bool)$options['keep_originals']
            : ($this->config ? $this->config->getKeepOriginals() : true);

        $removeIfLarger = isset($options['remove_if_larger'])
            ? (bool)$options['remove_if_larger']
            : ($this->config ? $this->config->getRemoveIfLarger() : false);

        return [
            'conversions' => $recommendations,
            'keep_original' => $keepOriginal,
            'remove_if_larger' => $removeIfLarger,
        ];
    }

    private function matchesFormat(string $mime, string $format): bool
    {
        $format = strtolower($format);
        if ($format === 'jpg') {
            $format = 'jpeg';
        }
        return $format !== '' && str_contains($mime, $format);
    }
}
