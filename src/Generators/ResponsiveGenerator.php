<?php
namespace Piplup\ImageX\Generators;

use Piplup\ImageX\Contracts\SrcsetGeneratorInterface;

class ResponsiveGenerator implements SrcsetGeneratorInterface
{
    /**
     * Build a srcset string from variants (array of ['url'|'src', 'width']).
     */
    public function generate(array $variants): string
    {
        $parts = [];
        foreach ($variants as $v) {
            $url = $v['url'] ?? ($v['src'] ?? null);
            $width = isset($v['width']) ? (int)$v['width'] : null;
            if ($url && $width) {
                $parts[] = sprintf('%s %dw', $url, $width);
            }
        }
        return implode(', ', $parts);
    }

    /**
     * Compute widths for responsive generation.
     * Supports explicit `widths`, `breakpoints`, and auto-generation.
     * Optionally accepts `dprs` (e.g. [1,2]) to include DPR-multiplied widths.
     */
    public function computeWidths(int $originalWidth, array $options = []): array
    {
        $widths = [];

        if (!empty($options['widths']) && is_array($options['widths'])) {
            $provided = array_values(array_unique(array_map('intval', $options['widths'])));
            foreach ($provided as $w) {
                if ($w > 0 && $w <= $originalWidth) {
                    $widths[] = $w;
                }
            }
        } elseif (!empty($options['breakpoints']) && is_array($options['breakpoints'])) {
            $bps = array_values(array_unique(array_map('intval', $options['breakpoints'])));
            foreach ($bps as $b) {
                if ($b > 0 && $b <= $originalWidth) {
                    $widths[] = $b;
                }
            }
        } else {
            // Auto: progressive scale (start 320, multiply by ~1.5 until >= original)
            $w = 320;
            while ($w < $originalWidth) {
                $widths[] = (int)$w;
                $w = (int) ceil($w * 1.5);
                if ($w <= 0) {
                    break;
                }
            }
        }

        // Always include original width as final candidate.
        $widths[] = $originalWidth;

        // DPR support: include multipliers if requested
        if (!empty($options['dprs']) && is_array($options['dprs'])) {
            $dprs = array_map('floatval', $options['dprs']);
            $extra = [];
            foreach ($widths as $base) {
                foreach ($dprs as $dpr) {
                    $candidate = (int) round($base * $dpr);
                    if ($candidate > 0 && $candidate <= $originalWidth) {
                        $extra[] = $candidate;
                    }
                }
            }
            $widths = array_merge($widths, $extra);
        }

        $widths = array_values(array_unique(array_filter($widths, function ($w) use ($originalWidth) {
            return $w > 0 && $w <= $originalWidth;
        })));
        sort($widths);
        return $widths;
    }
}
