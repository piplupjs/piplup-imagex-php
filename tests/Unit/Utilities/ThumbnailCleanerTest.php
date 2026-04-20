<?php
namespace Piplup\ImageX\Tests\Unit\Utilities;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Utilities\ThumbnailCleaner;

class ThumbnailCleanerTest extends TestCase
{
    private function makeTempDir(): string
    {
        $dir = sys_get_temp_dir() . '/imagex_thumb_test_' . uniqid();
        mkdir($dir, 0777, true);
        return $dir;
    }

    private function rrmdir(string $dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($dir);
    }

    public function testCleanupDeletesOldThumbnails()
    {
        $dir = $this->makeTempDir();
        $cleaner = new ThumbnailCleaner();

        file_put_contents($dir . '/orig.jpg', 'orig');
        $thumb1 = $dir . '/image-320w.webp';
        file_put_contents($thumb1, 'thumb1');
        $thumb2 = $dir . '/image-640w.webp';
        file_put_contents($thumb2, 'thumb2');

        // Make thumb1 old (10 days ago)
        touch($thumb1, time() - 3600 * 24 * 10);
        // thumb2 is recent

        $res = $cleaner->cleanup($dir, ['older_than' => 7 * 24 * 3600]);

        $this->assertContains($thumb1, $res['deleted']);
        $this->assertFileExists($thumb2);
        $this->assertFileExists($dir . '/orig.jpg');

        $this->rrmdir($dir);
    }

    public function testDryRunDoesNotDelete()
    {
        $dir = $this->makeTempDir();
        $cleaner = new ThumbnailCleaner();

        $thumb = $dir . '/a-320w.jpg';
        file_put_contents($thumb, 'x');
        touch($thumb, time() - 3600 * 24 * 10);

        $res = $cleaner->cleanup($dir, ['older_than' => 7 * 24 * 3600, 'dry_run' => true]);

        $this->assertContains($thumb, $res['deleted']);
        $this->assertFileExists($thumb, 'dry_run should not delete files');

        $this->rrmdir($dir);
    }

    public function testCustomPattern()
    {
        $dir = $this->makeTempDir();
        $cleaner = new ThumbnailCleaner();

        $f1 = $dir . '/thumb-1.tmp';
        $f2 = $dir . '/keep.me';
        file_put_contents($f1, 'x');
        file_put_contents($f2, 'y');
        touch($f1, time() - 3600 * 24 * 10);

        $res = $cleaner->cleanup($dir, ['pattern' => '/thumb-\d+\.tmp$/', 'older_than' => 1]);

        $this->assertContains($f1, $res['deleted']);
        $this->assertFileExists($f2);

        $this->rrmdir($dir);
    }
}
