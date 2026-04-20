<?php
namespace Piplup\ImageX\Watermark;

class Watermarker
{
    private $imagickFactory;

    public function __construct(?callable $imagickFactory = null)
    {
        $this->imagickFactory = $imagickFactory ?: function ($path) {
            return new \Imagick($path);
        };
    }

    /**
     * Apply a watermark image onto a target image path.
     *
     * Options:
     * - position: bottom-right (default), bottom-left, top-right, top-left, center
     * - opacity: 0-100 (default 100)
     * - scale: percentage of target width (e.g. 10 for 10%)
     * - margin: pixels offset from edges (default 10)
     */
    public function apply(string $targetPath, string $watermarkPath, array $options = []): bool
    {
        $factory = $this->imagickFactory;

        $image = $factory($targetPath);
        $watermark = $factory($watermarkPath);

        $imgW = method_exists($image, 'getImageWidth') ? $image->getImageWidth() : (method_exists($image, 'getWidth') ? $image->getWidth() : 0);
        $imgH = method_exists($image, 'getImageHeight') ? $image->getImageHeight() : (method_exists($image, 'getHeight') ? $image->getHeight() : 0);

        $wmW = method_exists($watermark, 'getImageWidth') ? $watermark->getImageWidth() : (method_exists($watermark, 'getWidth') ? $watermark->getWidth() : 0);
        $wmH = method_exists($watermark, 'getImageHeight') ? $watermark->getImageHeight() : (method_exists($watermark, 'getHeight') ? $watermark->getHeight() : 0);

        if (!empty($options['scale'])) {
            $scale = (float)$options['scale'];
            if ($imgW > 0 && $scale > 0) {
                $newW = (int)round($imgW * ($scale / 100.0));
                if (method_exists($watermark, 'scaleImage')) {
                    $watermark->scaleImage($newW, 0);
                } elseif (method_exists($watermark, 'resizeImage') && defined('Imagick::FILTER_LANCZOS')) {
                    $watermark->resizeImage($newW, 0, \Imagick::FILTER_LANCZOS, 1);
                }

                if (method_exists($watermark, 'getImageWidth')) {
                    $wmW = $watermark->getImageWidth();
                    $wmH = $watermark->getImageHeight();
                }
            }
        }

        $opacity = isset($options['opacity']) ? ((float)$options['opacity'] / 100.0) : 1.0;
        if ($opacity < 1.0) {
            if (method_exists($watermark, 'setImageOpacity')) {
                $watermark->setImageOpacity($opacity);
            } elseif (method_exists($watermark, 'setImageAlpha')) {
                $watermark->setImageAlpha($opacity);
            } elseif (method_exists($watermark, 'evaluateImage') && defined('\\Imagick::EVALUATE_MULTIPLY')) {
                $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);
            }
        }

        $position = $options['position'] ?? 'bottom-right';
        $margin = isset($options['margin']) ? (int)$options['margin'] : 10;

        switch ($position) {
            case 'top-left':
                $x = $margin;
                $y = $margin;
                break;
            case 'top-right':
                $x = max(0, $imgW - $wmW - $margin);
                $y = $margin;
                break;
            case 'bottom-left':
                $x = $margin;
                $y = max(0, $imgH - $wmH - $margin);
                break;
            case 'center':
                $x = max(0, intval(($imgW - $wmW) / 2));
                $y = max(0, intval(($imgH - $wmH) / 2));
                break;
            case 'bottom-right':
            default:
                $x = max(0, $imgW - $wmW - $margin);
                $y = max(0, $imgH - $wmH - $margin);
                break;
        }

        $compositeOp = defined('\\Imagick::COMPOSITE_OVER') ? \Imagick::COMPOSITE_OVER : 'COMPOSITE_OVER';

        if (!method_exists($image, 'compositeImage')) {
            throw new \RuntimeException('Provided image instance does not support compositeImage');
        }

        $image->compositeImage($watermark, $compositeOp, $x, $y);

        if (method_exists($image, 'writeImage')) {
            $image->writeImage($targetPath);
        }

        return true;
    }
}
