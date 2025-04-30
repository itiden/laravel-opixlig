# Opixlig

**Perfectly sized. Never pixelated.**

**Opixlig** is a Laravel-friendly image component inspired by modern frameworks like Next.js — designed to make responsive, optimized images effortless. It automatically generates and serves the right image size and format for every device, keeping your pages fast, sharp, and beautifully adaptive. Write clean Blade, and let **Opixlig** handle the rest.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itiden/opixlig.svg?style=flat-square)](https://packagist.org/packages/itiden/opixlig)
[![Total Downloads](https://img.shields.io/packagist/dt/itiden/opixlig.svg?style=flat-square)](https://packagist.org/packages/itiden/opixlig)
[![License](https://img.shields.io/packagist/l/itiden/opixlig.svg?style=flat-square)](https://packagist.org/packages/itiden/opixlig)

## Features

-   🖼️ **Responsive images**: Automatically generates and serves appropriately sized images for any device
-   🚀 **Performance optimized**: Converts images to modern formats like WebP for faster loading
-   📱 **Adaptive srcsets**: Creates optimized srcsets for both responsive and fixed-width images
-   🔍 **Placeholder support**: Includes "blur" and "empty" placeholder options while images load
-   ⚙️ **Highly configurable**: Customize quality, widths, and more to fit your needs
-   🛠️ **Simple API**: Clean Blade component syntax that feels natural in your Laravel views

## Installation

You can install the package via composer:

```bash
composer require itiden/opixlig
```

### Publishing the config

```bash
php artisan vendor:publish --tag="itiden-opixlig-config"
```

## Configuration

After publishing the config file, you can customize the following settings in `config/image.php`:

```php
return [
    // Where generated images are stored in your storage directory
    'storage_folder' => 'app/.images',

    // Public URL path to access the images
    'public_folder' => 'images',

    // Image manipulation driver ('imagick' or 'gd')
    'driver' => 'imagick',

    // Default widths for responsive images
    'default_widths' => [384, 640, 828, 1200, 2048, 3840],

    // Default placeholder type ('empty' or 'blur')
    'default_placeholder' => 'empty',

    // Default image quality (1-100)
    'default_quality' => 75,
];
```

## Usage

### Basic Example

Use the Blade component in your views:

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    alt="Hero image"
/>
```

### Responsive Images with Custom Sizes

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    sizes="(max-width: 768px) 100vw, 50vw"
    width="1200"
    height="800"
    alt="Responsive hero image"
/>
```

### Using Blur Placeholder

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    placeholder="blur"
    alt="Hero image with blur placeholder"
/>
```

### Custom Quality

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    quality="90"
    alt="High quality hero image"
/>
```

### Using the Helper Function

You can also use the `img()` helper function directly:

```php
$imageUrl = img('public/images/hero.jpg', 800, 600, ['fm' => 'webp', 'q' => 80])->url(['w' => 800]);
```

## Advanced Usage

### Available Props

| Prop        | Type   | Default                             | Description                                                                   |
| ----------- | ------ | ----------------------------------- | ----------------------------------------------------------------------------- |
| src         | string | ''                                  | Path to the source image (including disk name e.g., 'public/images/file.jpg') |
| sizes       | string | ''                                  | Media query sizes attribute for responsive images                             |
| width       | number | ''                                  | Width of the image                                                            |
| height      | number | ''                                  | Height of the image                                                           |
| loading     | string | 'lazy'                              | Image loading strategy ('lazy', 'eager', 'auto')                              |
| decoding    | string | 'async'                             | Image decoding strategy ('async', 'sync', 'auto')                             |
| quality     | number | config('image.default_quality')     | Image quality (1-100)                                                         |
| placeholder | string | config('image.default_placeholder') | Placeholder type ('empty' or 'blur')                                          |

### Image Manipulations

Opixlig uses [League's Glide](https://glide.thephpleague.com/) under the hood, so you can pass any Glide manipulation parameters:

```php
$imageUrl = img('public/images/hero.jpg', 800, 600)
    ->url([
        'w' => 800,               // Width
        'q' => 90,                // Quality
        'fm' => 'webp',           // Format
        'fit' => 'crop',          // Fit
        'crop' => 'center',       // Crop position
        // Other Glide parameters...
    ]);
```

## How It Works

1. The component generates optimized images on demand
2. Images are processed using the configured driver (Imagick or GD)
3. Processed images are stored in the configured cache directory
4. Subsequent requests for the same image with the same parameters are served directly from cache without hitting laravel
5. The package automatically generates appropriate srcsets for responsive designs

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
