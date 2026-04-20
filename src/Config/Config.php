<?php
namespace Piplup\ImageX\Config;

final class Config
{
    private int $defaultQuality;
    private bool $keepOriginals;
    private bool $removeIfLarger;

    public function __construct(int $defaultQuality = 75, bool $keepOriginals = true, bool $removeIfLarger = false)
    {
        $this->defaultQuality = $defaultQuality;
        $this->keepOriginals = $keepOriginals;
        $this->removeIfLarger = $removeIfLarger;
    }

    public static function fromArray(array $arr): self
    {
        return new self(
            isset($arr['quality']) ? (int)$arr['quality'] : (isset($arr['default_quality']) ? (int)$arr['default_quality'] : 75),
            isset($arr['keep_originals']) ? (bool)$arr['keep_originals'] : true,
            isset($arr['remove_if_larger']) ? (bool)$arr['remove_if_larger'] : false
        );
    }

    public function getDefaultQuality(): int
    {
        return $this->defaultQuality;
    }

    public function getKeepOriginals(): bool
    {
        return $this->keepOriginals;
    }

    public function getRemoveIfLarger(): bool
    {
        return $this->removeIfLarger;
    }
}
