---
name: opixlig-development
description: Work with Opixlig responsive image components — adding images, configuring presets, using placeholders, fit/crop options, and the img() helper.
---

# Opixlig Development

## When to use this skill

Use this skill when adding or modifying images using `<x-opixlig::image>`, configuring presets in `config/opixlig.php`, using the `img()` helper, or working with Statamic assets through Opixlig.

## Critical: `src` path format

The `src` prop must include the storage **disk name** as the first path segment:

```blade
{{-- Correct: disk name "public" + path --}}
<x-opixlig::image src="public/images/hero.jpg" ... />

{{-- Statamic: disk = asset container handle --}}
<x-opixlig::image src="assets/images/hero.jpg" ... />

{{-- Wrong: missing disk prefix --}}
<x-opixlig::image src="images/hero.jpg" ... />
```

## Component Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `src` | string | required | Path including disk name prefix (e.g. `public/images/file.jpg`) |
| `alt` | string | required | Alt text for the image |
| `width` | number | — | Must be set with `height`. Omit both for a plain `<img>` |
| `height` | number | — | Must be set with `width`. Omit both for a plain `<img>` |
| `preset` | string | — | Name of a preset from `config('opixlig.presets')` |
| `sizes` | string | — | Media query sizes attribute for responsive images |
| `loading` | string | `'lazy'` | `'lazy'`, `'eager'`, `'auto'` |
| `decoding` | string | `'async'` | `'async'`, `'sync'`, `'auto'` |
| `quality` | number | config default (75) | Image quality 1–100 |
| `placeholder` | string | config default (`'empty'`) | `'empty'` or `'blur'` |
| `format` | string | config default (`'webp'`) | `'webp'`, `'avif'`, `'jpg'`, `'pjpg'`, `'png'`, `'gif'`, `'heic'` |
| `fit` | string | — | Resize/crop method (see below) |
| `manipulations` | array | `[]` | Additional Glide params as key-value pairs |

## Common Examples

### Responsive hero image

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    sizes="(max-width: 768px) 100vw, 50vw"
    width="1200"
    height="630"
    alt="Hero image"
/>
```

### Blur placeholder while loading

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    placeholder="blur"
    alt="Hero image"
/>
```

### Center-cropped thumbnail

```blade
<x-opixlig::image
    src="public/images/profile.jpg"
    width="400"
    height="400"
    fit="crop-center"
    alt="Profile photo"
/>
```

### Statamic asset with focal point

```blade
{{-- Statamic focal point crops use the format crop-{x}-{y}-{zoom} --}}
<x-opixlig::image
    src="assets/{{ $asset->path() }}"
    width="1200"
    height="630"
    :fit="'crop-' . $asset->get('focus')"
    alt="{{ $asset->alt }}"
/>
```

### Using a preset

```blade
<x-opixlig::image src="public/images/profile.jpg" preset="avatar" alt="User avatar" />

{{-- Inline props override preset values --}}
<x-opixlig::image src="public/images/profile.jpg" preset="avatar" width="128" height="128" alt="Large avatar" />
```

## Fit Values

| Value | Description |
|---|---|
| `contain` | Resize to fit within dimensions, preserving aspect ratio |
| `max` | Resize to fit within dimensions, no upscaling |
| `fill` | Resize and pad to fill dimensions |
| `stretch` | Stretch to fill dimensions exactly |
| `crop` | Crop to fill dimensions |
| `crop-center` | Crop from center |
| `crop-top` | Crop from top |
| `crop-bottom` | Crop from bottom |
| `crop-left` | Crop from left |
| `crop-right` | Crop from right |
| `crop-{x}-{y}-{zoom}` | Statamic focal point (e.g. `crop-44-13-1`) |

## Additional Manipulations

Pass any [Glide manipulation](https://glide.thephpleague.com/2.0/api/quick-reference/) via the `:manipulations` prop:

```blade
<x-opixlig::image
    src="public/images/photo.jpg"
    width="800"
    height="600"
    :manipulations="['sharp' => 50, 'filt' => 'greyscale', 'blur' => 5]"
    alt="Processed image"
/>
```

Common manipulation keys: `blur` (0–100), `sharp` (0–100), `bri` (brightness -100–100), `con` (contrast -100–100), `filt` (`greyscale`|`sepia`), `flip` (`h`|`v`|`both`), `bg` (hex color).

## Defining Presets

Add presets to `config/opixlig.php`. Supports friendly names and Glide shorthand:

```php
'presets' => [
    'avatar' => [
        'width' => 64,      // or 'w' => 64
        'height' => 64,     // or 'h' => 64
        'quality' => 70,    // or 'q' => 70
        'fit' => 'crop-center',
        'placeholder' => 'blur',
    ],
    'hero' => [
        'width' => 1200,
        'height' => 630,
        'format' => 'avif', // or 'fm' => 'avif'
        'quality' => 85,
        'widths' => [600, 900, 1200],
    ],
    'og-image' => [
        'w' => 1200,
        'h' => 630,
        'fm' => 'jpg',
        'q' => 80,
    ],
],
```

## The `img()` Helper

Use `img()` to generate image URLs in PHP/Blade logic:

```php
// Generate a URL directly
$url = img('public/images/hero.jpg', 800, 600)->url(['w' => 800]);

// With Glide params
$url = img('public/images/hero.jpg', 800, 600, ['fm' => 'webp', 'q' => 80])->url(['w' => 800]);

// With a preset
$url = img('public/images/profile.jpg', preset: 'avatar')->url(['w' => 64]);
```

## Configuration Reference

```php
// config/opixlig.php
return [
    'storage_folder' => 'app/opixlig',   // Where cached images are stored
    'public_folder'  => 'images',         // Public URL path prefix
    'driver'         => 'imagick',        // 'imagick' or 'gd'

    'defaults' => [
        'widths'      => [384, 640, 828, 1200, 1920, 2048, 3840],
        'placeholder' => 'empty',         // 'empty' or 'blur'
        'quality'     => 75,
        'format'      => 'webp',
    ],

    'presets' => [],
];
```

Publish the config: `php artisan vendor:publish --tag="itiden-opixlig-config"`
