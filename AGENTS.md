# AGENTS.md

## Project Overview

**Opixlig** (`itiden/opixlig`) is a Laravel package that provides responsive, optimized image components inspired by Next.js `<Image>`. It generates appropriately sized images on demand, converts to modern formats (WebP, AVIF), and serves them with proper srcsets. Uses League/Glide for image manipulation.

## Tech Stack

- **Language**: PHP 8.1+
- **Framework**: Laravel 10/11/12 (package, not a full app)
- **Image Processing**: League/Glide
- **Templating**: Blade components
- **Testing**: Pest (with type coverage, min 100% coverage required)
- **Static Analysis**: PHPStan (level max)
- **Code Style**: Laravel Pint
- **Refactoring**: Rector (dead code, code quality, type declarations, privatization, early return, strict booleans)

## Architecture

```
src/
  Http/Controllers/ImageController.php   # Serves processed images via route, validates Glide signatures
  Jobs/DeleteCachedImage.php             # Queued job to clear cached images when assets change
  Listeners/StatamicAssetSubscriber.php  # Listens for Statamic asset events to bust cache
  Services/ImageService.php              # Core service: URL generation with Glide signatures, placeholder CSS
  Services/Placeholder.php              # Generates blur placeholder SVGs (ported from Next.js)
  Utils/utils.php                        # Global `img()` helper function
  OpixligServiceProvider.php             # Registers config, views, routes, filesystem links
config/opixlig.php                       # Package configuration
resources/views/components/image.blade.php  # <x-opixlig::image> Blade component
routes/web.php                           # Image serving route with signature validation
```

### Key Flows

1. **Blade component** (`<x-opixlig::image>`) calls `img()` helper -> `ImageService` -> generates signed URLs with Glide manipulation params
2. **Image serving**: Browser requests signed URL -> `ImageController` validates signature, uses Glide to process image, saves to public path, returns file. Subsequent requests are served directly by the web server (bypassing Laravel).
3. **Cache invalidation**: Statamic asset events -> `StatamicAssetSubscriber` -> dispatches `DeleteCachedImage` job

### URL Structure

`/{public_folder}/{source_path}/{manipulation-string}/{filename}.{format}?s={signature}`

Manipulations are serialized as sorted key-value pairs joined by hyphens (e.g., `fm-webp-q-75-w-800`).

## Coding Conventions

- All classes are `final`
- PSR-4 autoloading under `Itiden\Opixlig\` namespace
- 4-space indentation, LF line endings
- Strict typing where possible (Rector enforces type declarations)
- Config accessed via `Config::get('opixlig.*')`
- Image source paths include the storage disk name (e.g., `public/images/file.jpg`)

## Commands

```bash
# Run all checks (rector, pint, phpstan, pest)
composer test

# Individual checks
composer test:unit      # Pest tests with coverage (--min=100)
composer test:types     # PHPStan analysis
composer test:lint      # Pint style check
composer test:refacto   # Rector dry-run

# Fix style/refactor
composer lint           # Pint fix
composer refacto        # Rector fix
```

## Testing

Tests use Pest and live in `tests/`. Currently minimal (`tests/Feature.php`). The project requires 100% code coverage (`--min=100`). When adding features, add corresponding tests.

## Important Details

- The `app.key` is used as the Glide signature key — URLs are signed to prevent unauthorized image manipulation
- Processed images are cached to the public directory so the web server can serve them directly on subsequent requests
- The package integrates with Statamic's asset pipeline but works standalone with any Laravel storage disk
- Placeholder blur SVGs are based on Next.js implementation (`image-blur-svg.ts`)
