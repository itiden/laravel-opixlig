## Opixlig

Opixlig is a Laravel package that provides a responsive, optimized image Blade component inspired by Next.js `<Image>`. It generates appropriately sized images on demand, converts to modern formats (WebP, AVIF), and serves them with proper srcsets using League/Glide for image manipulation.

### Installation

```bash
composer require itiden/opixlig
php artisan vendor:publish --tag="itiden-opixlig-config"
```

### Critical Convention: `src` path format

The `src` prop **must** include the storage disk name as a prefix:

```blade
{{-- Correct --}}
<x-opixlig::image src="public/images/hero.jpg" ... />

{{-- Wrong - missing disk prefix --}}
<x-opixlig::image src="images/hero.jpg" ... />
```

For Statamic assets, the disk name matches the asset container handle (e.g., `assets/path/to/image.jpg`).

### Basic Usage

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="1200"
    height="630"
    alt="Hero image"
/>
```

`width` and `height` must always be provided together or omitted entirely — providing only one throws an `InvalidArgumentException`.

### Key Features

- Automatic srcset generation with configurable widths
- `placeholder="blur"` for blur-up loading effect
- `fit` prop for cropping (including Statamic focal points: `fit="crop-44-13-1"`)
- `preset` prop for reusable image configurations defined in config
- `img()` global helper for generating image URLs in PHP code
- Processed images are cached to disk and served directly by the web server on subsequent requests
