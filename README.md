# Opixlig

**Perfectly sized. Never pixelated.**

**Opixlig** is a Laravel-friendly image component inspired by modern frameworks like Next.js — designed to make responsive, optimized images effortless. It automatically generates and serves the right image size and format for every device, keeping your pages fast, sharp, and beautifully adaptive. Write clean Blade, and let **Opixlig** handle the rest.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itiden/opixlig.svg?style=flat-square)](https://packagist.org/packages/itiden/opixlig)
[![Total Downloads](https://img.shields.io/packagist/dt/itiden/opixlig.svg?style=flat-square)](https://packagist.org/packages/itiden/opixlig)
[![License](https://img.shields.io/packagist/l/itiden/opixlig.svg?style=flat-square)](https://packagist.org/packages/itiden/opixlig)

## Features

- 🖼️ **Responsive images**: Automatically generates and serves appropriately sized images for any device
- 🚀 **Performance optimized**: Converts images to modern formats like WebP for faster loading
- 📱 **Adaptive srcsets**: Creates optimized srcsets for both responsive and fixed-width images
- 🔍 **Placeholder support**: Includes "blur" and "empty" placeholder options while images load
- ⚙️ **Highly configurable**: Customize quality, widths, and more to fit your needs
- 🛠️ **Simple API**: Clean Blade component syntax that feels natural in your Laravel views

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

After publishing the config file, you can customize the following settings in `config/opixlig.php`:

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

    // Default image format ('webp', 'avif', 'png', 'jpg', 'pjpg', 'gif', 'heic')
    'default_format' => 'webp',
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

### Cropped Image

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    fit="crop-center"
    alt="Center-cropped hero image"
/>
```

### Additional Manipulations

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    :manipulations="['blur' => 10, 'bri' => 20]"
    alt="Blurred and brightened hero image"
/>
```

### Using the Helper Function

You can also use the `img()` helper function directly:

```php
$imageUrl = img('public/images/hero.jpg', 800, 600, ['fm' => 'webp', 'q' => 80])->url(['w' => 800]);
```

## Advanced Usage

### Available Props

| Prop          | Type   | Default                               | Description                                                                                                                                                                                                               |
| ------------- | ------ | ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| src           | string | ''                                    | Path to the source image (including disk name e.g., 'public/images/file.jpg')                                                                                                                                             |
| sizes         | string | ''                                    | Media query sizes attribute for responsive images                                                                                                                                                                         |
| width         | number | ''                                    | Width of the image                                                                                                                                                                                                        |
| height        | number | ''                                    | Height of the image                                                                                                                                                                                                       |
| loading       | string | 'lazy'                                | Image loading strategy ('lazy', 'eager', 'auto')                                                                                                                                                                          |
| decoding      | string | 'async'                               | Image decoding strategy ('async', 'sync', 'auto')                                                                                                                                                                         |
| quality       | number | config('opixlig.default_quality')     | Image quality (1-100)                                                                                                                                                                                                     |
| placeholder   | string | config('opixlig.default_placeholder') | Placeholder type ('empty' or 'blur')                                                                                                                                                                                      |
| format        | string | config('opixlig.default_format')      | Image format ('jpg', 'pjpg', 'png', 'gif', 'webp', 'avif', 'heic'*). *heic only supported with Imagick driver                                                                                                             |
| fit           | string | ''                                    | How the image is fitted to its target dimensions. Values: 'contain', 'max', 'fill', 'fill-max', 'stretch', 'crop'. For crop positioning, use e.g. 'crop-center', 'crop-top', or Statamic focal points like 'crop-44-13-1' |
| manipulations | array  | []                                    | Additional [Glide manipulations](https://glide.thephpleague.com/2.0/api/quick-reference/) as key-value pairs                                                                                                              |

### Image Manipulations

The `fit` prop covers the most common resize/crop use cases (including Statamic focal point crops like `fit="crop-44-13-1"`), but you can pass any [Glide manipulation parameter](https://glide.thephpleague.com/2.0/api/quick-reference/) via the `manipulations` prop:

```blade
<x-opixlig::image
    src="public/images/hero.jpg"
    width="800"
    height="600"
    fit="crop-center"
    :manipulations="['sharp' => 50, 'filt' => 'greyscale']"
    alt="Cropped greyscale hero"
/>
```

All supported Glide manipulations:

| Parameter   | Key      | Description                                                |
| ----------- | -------- | ---------------------------------------------------------- |
| Width       | `w`      | Width in pixels (set via `width` prop)                     |
| Height      | `h`      | Height in pixels (set via `height` prop)                   |
| Fit         | `fit`    | Resize method (set via `fit` prop)                         |
| Crop        | `crop`   | Pixel-based crop: 'width,height,x,y' (via `manipulations`) |
| Quality     | `q`      | Image quality, 0-100 (set via `quality` prop)              |
| Format      | `fm`     | Output format (set via `format` prop)                      |
| Blur        | `blur`   | Blur amount (0-100)                                        |
| Sharpen     | `sharp`  | Sharpen amount (0-100)                                     |
| Brightness  | `bri`    | Brightness adjustment (-100 to 100)                        |
| Contrast    | `con`    | Contrast adjustment (-100 to 100)                          |
| Gamma       | `gam`    | Gamma adjustment (0.1 to 9.99)                             |
| Pixelate    | `pixel`  | Pixelate amount (0-1000)                                   |
| Filter      | `filt`   | Filter ('greyscale', 'sepia')                              |
| Flip        | `flip`   | Flip ('h', 'v', 'both')                                    |
| Orientation | `or`     | Rotation ('auto', '90', '180', '270')                      |
| Background  | `bg`     | Background color (hex, e.g. 'fff')                         |
| Border      | `border` | Border ('width,color,method')                              |

You can also use the `img()` helper function directly:

```php
$imageUrl = img('public/images/hero.jpg', 800, 600, ['fm' => 'webp', 'q' => 80])->url(['w' => 800]);
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
