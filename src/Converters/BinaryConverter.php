<?php
namespace Piplup\ImageX\Converters;

use Piplup\ImageX\Contracts\ConverterInterface;

class BinaryConverter implements ConverterInterface
{
    public function __construct()
    {
    }

    public function convert(string $sourcePath, string $targetPath, array $options = []): void
    {
        $format = strtolower($options['format'] ?? pathinfo($targetPath, PATHINFO_EXTENSION));

        // Try command-line tools first
        if ($format === 'webp' && $this->isCommandAvailable('cwebp')) {
            $quality = isset($options['quality']) ? (int)$options['quality'] : 80;
            $cmd = sprintf('cwebp -q %d %s -o %s 2>&1', $quality, escapeshellarg($sourcePath), escapeshellarg($targetPath));
            exec($cmd, $out, $rc);
            if ($rc === 0 && file_exists($targetPath)) {
                return;
            }
        }

        if ($format === 'avif' && $this->isCommandAvailable('avifenc')) {
            $quality = isset($options['quality']) ? (int)$options['quality'] : 50;
            $cmd = sprintf('avifenc --min 0 --max 63 -q %d %s %s 2>&1', $quality, escapeshellarg($sourcePath), escapeshellarg($targetPath));
            exec($cmd, $out, $rc);
            if ($rc === 0 && file_exists($targetPath)) {
                return;
            }
        }

        // Try Imagick if available
        if (class_exists('Imagick')) {
            $im = new \Imagick($sourcePath);
            if (!empty($options['format'])) {
                $im->setImageFormat($options['format']);
            }
            if (isset($options['quality'])) {
                $im->setImageCompressionQuality((int)$options['quality']);
            }
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $im->writeImage($targetPath);
            $im->clear();
            $im->destroy();
            if (file_exists($targetPath)) {
                return;
            }
        }

        // Final fallback: copy
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        copy($sourcePath, $targetPath);
    }

    private function isCommandAvailable(string $name): bool
    {
        $which = PHP_OS_FAMILY === 'Windows' ? 'where' : 'command -v';
        $cmd = $which . ' ' . escapeshellarg($name) . ' 2>&1';
        $out = shell_exec($cmd);
        return !empty($out);
    }
}
