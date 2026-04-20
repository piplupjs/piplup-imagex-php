<?php
namespace Piplup\ImageX\Contracts;

interface SrcsetGeneratorInterface
{
    /**
     * Produce a srcset string given variants with 'url' and 'width' keys.
     *
     * @param array $variants
     * @return string
     */
    public function generate(array $variants): string;

    /**
     * Compute a set of widths for responsive generation.
     *
     * @param int $originalWidth
     * @param array $options
     * @return int[]
     */
    public function computeWidths(int $originalWidth, array $options = []): array;
}
