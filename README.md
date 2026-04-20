# Piplup ImageX

Headless PHP image optimization library — generate responsive variants, convert formats, and manage storage/CDN integration.

## Table of contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [API Reference](#api-reference)
  - [Image attributes](#image-attributes)
  - [Variant generation](#variant-generation)
  - [Storage adapters](#storage-adapters)
  - [Converters & Resizers](#converters--resizers)
  - [Compression helpers](#compression-helpers)
  - [CLI](#cli)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Overview

`Piplup/ImageX` is a small, framework-agnostic PHP library that helps you:

- Inspect images and produce `srcset`-ready attributes.
- Generate responsive variants, store them (local or S3), and produce CDN-ready URLs.
- Convert and compress images using either binary tools, Imagick or simple copy fallbacks.
- Provide a tiny CLI for simple automation.

The library is intentionally small and explicit — it provides building blocks that you can compose in your application.

## Features

- Compute `src`, `srcset`, `sizes`, `width`, `height`, and MIME `type` for images.
- Generate responsive variants and store them with pluggable storage adapters.
- Multiple converter implementations: Imagick, binary-command based, and a null (copy) converter.
- Resizer that uses Imagick when available and falls back to GD.
- Smart compression recommendations driven by configurable compression profiles.
- Built-in CLI: `generate`, `convert`, `regenerate`.

## Requirements

- PHP 8.0 or later (see `composer.json`).
- Optional but recommended: `ext-imagick` for best quality image ops.
- Optional CLI tools for some conversions: `cwebp`, `avifenc` (used by `BinaryConverter`).

## Installation

Install via Composer:

```bash
composer require piplup/imagex
```

This package follows PSR-4 autoloading under the `Piplup\ImageX\` namespace.

## Quick start

1) Simple attributes extraction (generates `srcset` automatically):

```php
use Piplup\ImageX\ImageManager;

$mgr = new ImageManager();
$attrs = $mgr->getAttributes('/path/to/image.jpg', [
	'widths' => [320, 640, 1024],
	'sizes' => '(max-width: 600px) 100vw, 50vw',
]);

// $attrs contains: src, srcset, sizes, width, height, type
print_r($attrs);
```

2) Generate responsive variants and store them (local storage + binary converter example):

```php
use Piplup\ImageX\Resizer\SimpleResizer;
use Piplup\ImageX\Adapters\LocalStorageAdapter;
use Piplup\ImageX\Converters\BinaryConverter;
use Piplup\ImageX\Manager\VariantManager;

$resizer = new SimpleResizer();
$storage = new LocalStorageAdapter(__DIR__ . '/public/uploads', 'https://example.com/uploads');
$converter = new BinaryConverter();

$vm = new VariantManager($resizer, $storage, $converter);
$variants = $vm->generateVariants('/path/to/image.jpg', [
	'format' => 'webp',
	'storage_path' => 'images/variants',
	'widths' => [320, 640, 1280],
]);

print_r($variants);
// Each variant: ['url' => ..., 'width' => 320, 'path' => 'images/variants/2026/04/name-320w.webp', 'created' => true]
```

## API Reference

Below are the main classes and how to use them. For more details see the source files in `src/`.

### Image attributes

- `Piplup\ImageX\ImageManager` — convenience wrapper to get image attributes. See [src/ImageManager.php](src/ImageManager.php).
- `Piplup\ImageX\ImageAttributesFactory` — main logic for `getAttributes(string $path, array $options = [])`. Returns an array with keys: `src`, `srcset`, `sizes`, `width`, `height`, `type`. See [src/ImageAttributesFactory.php](src/ImageAttributesFactory.php).

Options supported by `getAttributes` include:
- `widths`: array of integers (explicit widths to include in `srcset`).
- `sizes`: string for the `sizes` attribute (defaults to `100vw`).
- `generated_variants`: an array of pre-generated variant records (will be used instead of computing local widths).
- `storage_adapter`: instance implementing `Piplup\ImageX\Contracts\StorageAdapterInterface` (used to map stored paths to URLs).

### Variant generation

- `Piplup\ImageX\Manager\VariantManager` — generate or ensure responsive variants exist and are stored via a `StorageAdapterInterface`. See [src/Manager/VariantManager.php](src/Manager/VariantManager.php).

Key options for `ensureVariants` / `generateVariants`:
- `storage_path`: base path used in storage for generated variants (default `uploads/variants`).
- `format`: desired output extension/format (e.g. `webp`).
- `widths`, `breakpoints`, `dprs`: forwarded to the generator.
- `resize_options`: options passed to the resizer (e.g. `['quality'=>80, 'format'=>'webp']`).

Return value: array of variant metadata: `['url' => string, 'width' => int, 'path' => string, 'created' => bool]`.

### Storage adapters

- `Piplup\ImageX\Adapters\LocalStorageAdapter` — store files on local disk and optionally map to a `baseUrl`. See [src/Adapters/LocalStorageAdapter.php](src/Adapters/LocalStorageAdapter.php).
- `Piplup\ImageX\Adapters\S3StorageAdapter` — store via an S3-compatible client (expects an SDK client). See [src/Adapters/S3StorageAdapter.php](src/Adapters/S3StorageAdapter.php).

Both adapters implement `Piplup\ImageX\Contracts\StorageAdapterInterface` with methods: `put`, `exists`, `url`, `get`, `delete`.

### Converters & Resizers

- `Piplup\ImageX\Converters\ImagickConverter` — uses `ext-imagick` to convert images (falls back to copy when unavailable). See [src/Converters/ImagickConverter.php](src/Converters/ImagickConverter.php).
- `Piplup\ImageX\Converters\BinaryConverter` — attempts to use command-line tools (`cwebp`, `avifenc`) then Imagick then copy. See [src/Converters/BinaryConverter.php](src/Converters/BinaryConverter.php).
- `Piplup\ImageX\Converters\NullConverter` — simple copy converter.
- `Piplup\ImageX\Resizer\SimpleResizer` — resizes images using Imagick when available, otherwise uses GD. See [src/Resizer/SimpleResizer.php](src/Resizer/SimpleResizer.php).

The converter interface is `Piplup\ImageX\Contracts\ConverterInterface::convert(string $sourcePath, string $targetPath, array $options = [])`.

### Compression helpers

- `Piplup\ImageX\Compression\CompressionProfile` — configure which formats to recommend and their thresholds. See [src/Compression/CompressionProfile.php](src/Compression/CompressionProfile.php).
- `Piplup\ImageX\Compression\SmartCompression` — inspect metadata (mime, size) and return recommendations: `['conversions'=>[['format'=>'webp','quality'=>80],...],'keep_original'=>bool,'remove_if_larger'=>bool]`. See [src/Compression/SmartCompression.php](src/Compression/SmartCompression.php).

Example:

```php
use Piplup\ImageX\Compression\CompressionProfile;
use Piplup\ImageX\Compression\SmartCompression;
use Piplup\ImageX\Config\Config;

$profile = CompressionProfile::fromArray([
	'webp' => ['quality' => 80, 'min_size' => 10240],
	'avif' => ['quality' => 50, 'min_size' => 32768],
]);

$smart = new SmartCompression($profile, Config::fromArray(['quality' => 75]));
$rec = $smart->recommend(['mime' => 'image/jpeg', 'size' => filesize('/path/to/image.jpg')]);
print_r($rec);
```

### CLI

There is a tiny CLI entrypoint at `bin/compressx` that exposes three commands:

- `generate`: basic front for `ImageManager` (can use `IMAGE_MANAGER_CLASS` env to point to custom implementation).
- `convert`: run conversion helper commands.
- `regenerate`: placeholder to trigger regeneration flows.

Example:

```bash
php bin/compressx generate /path/to/image.jpg --widths=320,640 --sizes='100vw'

# Use a custom manager implementation via env:
IMAGE_MANAGER_CLASS=\MyApp\CustomImageManager php bin/compressx generate /path/to/image.jpg
```

The CLI commands are intentionally minimal and return JSON to make automation simple.

## Testing

Unit tests are available under `tests/`. Run the test suite with Composer:

```bash
composer install --dev
composer test
```

Or directly:

```bash
vendor/bin/phpunit
```

## Contributing

- Fork and send a PR.
- Keep changes small and focused.
- Run tests before submitting.

## Troubleshooting & notes

- If `ext-imagick` is not installed, the library falls back to GD or simple copy behavior — some features and quality optimizations will be limited.
- `BinaryConverter` will attempt to use `cwebp` and `avifenc` if present on the system `PATH`.
- `S3StorageAdapter` expects a working S3-like client (AWS SDK or compatible). It uses method names such as `putObject`, `getObject` or `headObject` depending on the client implementation.

## License

This project is licensed under the MIT License. See `composer.json` for package metadata.


