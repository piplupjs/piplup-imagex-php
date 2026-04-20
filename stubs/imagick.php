<?php
// Lightweight Imagick stub for static analysis and test environments.
// Define only if the extension class is not available.
if (!class_exists('Imagick')) {
    class Imagick
    {
        public const COMPOSITE_OVER = 1;
        public const FILTER_LANCZOS = 2;
        public const EVALUATE_MULTIPLY = 3;
        public const CHANNEL_ALPHA = 4;
        public function __construct($path = null) {}
        public function getImageWidth() { return 0; }
        public function getImageHeight() { return 0; }
        public function scaleImage($w, $h) {}
        public function resizeImage($w, $h, $filter = null, $blur = 1) {}
        public function setImageOpacity($opacity) {}
        public function setImageAlpha($alpha) {}
        public function evaluateImage($op, $value, $channel = null) {}
        public function compositeImage($other, $op, $x, $y) {}
        public function writeImage($path = null) {}
    }
}
