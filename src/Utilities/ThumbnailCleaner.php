<?php
namespace Piplup\ImageX\Utilities;

class ThumbnailCleaner
{
    /**
     * Remove thumbnail/variant files from a directory tree.
     *
     * Options:
     * - pattern: regex to match thumbnail filenames (default: /-\d+w\.(webp|avif|jpe?g|png)$/i)
     * - older_than: seconds; only remove files older than this (default: 0 = remove regardless of age)
     * - dry_run: bool; if true, do not actually delete, only return matches (default: false)
     *
     * Returns array with keys `deleted` and `skipped` containing full paths.
     */
    public function cleanup(string $directory, array $options = []): array
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException('Directory not found: ' . $directory);
        }

        $pattern = $options['pattern'] ?? '/-\d+w\.(webp|avif|jpe?g|png)$/i';
        $olderThan = isset($options['older_than']) ? (int)$options['older_than'] : 0;
        $dryRun = !empty($options['dry_run']);

        $deleted = [];
        $skipped = [];

        $now = time();

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $basename = $file->getBasename();
            if (!preg_match($pattern, $basename)) {
                continue;
            }

            $mtime = $file->getMTime();
            if ($olderThan > 0 && ($now - $mtime) < $olderThan) {
                $skipped[] = $file->getPathname();
                continue;
            }

            if ($dryRun) {
                $deleted[] = $file->getPathname();
                continue;
            }

            if (@unlink($file->getPathname())) {
                $deleted[] = $file->getPathname();
            } else {
                $skipped[] = $file->getPathname();
            }
        }

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }
}
