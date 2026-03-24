<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\ViewException;

it('renders an img tag with expected base attributes', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" alt="Example image" />');

    expect($html)
        ->toContain('<img')
        ->toContain('alt="Example image"')
        ->toContain('src="http://localhost/images/public/images/example.jpg/')
        ->toContain('width="320"')
        ->toContain('height="180"');
});

it('does not include fit in manipulation string when fit is empty', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" />');

    expect($html)
        ->toContain('fm-webp')
        ->toContain('q-75')
        ->not->toContain('fit-');
});

it('includes fit in manipulation string when fit is set', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" fit="crop-center" />');

    expect($html)->toContain('fit-crop-center');
});

it('includes focal point fit values in manipulation string', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" fit="crop-44-13-1" />');

    expect($html)->toContain('fit-crop-44-13-1');
});

it('merges additional manipulations into the manipulation string', function (): void {
    $template = <<<'BLADE'
<x-opixlig::image
    src="public/images/example.jpg"
    width="320"
    height="180"
    :manipulations="['blur' => 10, 'bri' => 20]"
/>
BLADE;

    $html = Blade::render($template);

    expect($html)
        ->toContain('blur-10')
        ->toContain('bri-20')
        ->toContain('fm-webp')
        ->toContain('q-75');
});

it('supports custom format and quality values in manipulation string', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" format="avif" quality="60" />');

    expect($html)
        ->toContain('fm-avif')
        ->toContain('q-60');
});

it('generates responsive srcset with width descriptors when sizes is provided', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="800" height="600" sizes="(max-width: 768px) 100vw, 50vw" />');

    expect($html)
        ->toContain('sizes="(max-width: 768px) 100vw, 50vw"')
        ->toContain('srcset="')
        ->toContain(' 384w')
        ->toContain('h-288')
        ->toContain(' 3840w')
        ->toContain('h-2880');
});

it('generates fixed width srcset with 1x and 2x descriptors', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="300" height="150" />');

    expect($html)
        ->toContain('srcset="')
        ->toContain(' 1x')
        ->toContain(' 2x')
        ->toContain('w-300')
        ->toContain('h-150')
        ->toContain('w-600')
        ->toContain('h-300');
});

it('renders loading and decoding attributes', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="300" height="150" loading="eager" decoding="sync" />');

    expect($html)
        ->toContain('loading="eager"')
        ->toContain('decoding="sync"');
});

it('does not render placeholder style when placeholder is empty', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="300" height="150" placeholder="empty" />');

    expect($html)->not->toContain('style=');
});

it('passes additional HTML attributes through to the img tag', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="300" height="150" class="rounded" id="hero" />');

    expect($html)
        ->toContain('class="rounded"')
        ->toContain('id="hero"');
});

it('renders src-only output when no width or sizes are provided', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" />');

    expect($html)
        ->toContain('src="http://localhost/images/public/images/example.jpg/')
        ->toContain('fm-webp')
        ->not->toContain('srcset=');
});

it('renders blur placeholder style attribute when placeholder is blur', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required.');
    }

    $resource = imagecreatetruecolor(10, 10);
    $color = imagecolorallocate($resource, 200, 100, 50);
    imagefill($resource, 0, 0, $color);

    ob_start();
    imagepng($resource);
    $image = (string) ob_get_clean();
    imagedestroy($resource);

    Storage::disk('public')->put('blade-tests/photo.png', $image);

    $html = Blade::render('<x-opixlig::image src="public/blade-tests/photo.png" width="200" height="100" placeholder="blur" />');

    expect($html)
        ->toContain('style="background-image:url(')
        ->toContain('background-size:cover')
        ->toContain('background-position:center');

    Storage::disk('public')->deleteDirectory('blade-tests');
});

it('applies preset width and height to the image', function (): void {
    config()->set('opixlig.presets.thumb', [
        'w' => 150,
        'h' => 100,
        'q' => 60,
        'fit' => 'crop-center',
    ]);

    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" preset="thumb" />');

    expect($html)
        ->toContain('width="150"')
        ->toContain('height="100"')
        ->toContain('q-60')
        ->toContain('fit-crop-center');
});

it('applies preset using friendly aliases', function (): void {
    config()->set('opixlig.presets.hero', [
        'width' => 1200,
        'height' => 630,
        'quality' => 85,
        'format' => 'avif',
    ]);

    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" preset="hero" />');

    expect($html)
        ->toContain('width="1200"')
        ->toContain('height="630"')
        ->toContain('q-85')
        ->toContain('fm-avif');
});

it('allows inline props to override preset values', function (): void {
    config()->set('opixlig.presets.thumb', [
        'w' => 150,
        'h' => 100,
        'q' => 60,
        'fm' => 'webp',
    ]);

    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" preset="thumb" quality="90" format="avif" />');

    expect($html)
        ->toContain('q-90')
        ->toContain('fm-avif')
        ->toContain('width="150"');
});

it('uses preset widths for responsive srcset', function (): void {
    config()->set('opixlig.presets.banner', [
        'widths' => [400, 800],
        'w' => 800,
        'h' => 400,
    ]);

    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" preset="banner" sizes="100vw" />');

    expect($html)
        ->toContain(' 400w')
        ->toContain(' 800w')
        ->not->toContain(' 3840w');
});

it('throws exception for undefined preset', function (): void {
    Blade::render('<x-opixlig::image src="public/images/example.jpg" preset="nonexistent" />');
})->throws(ViewException::class, "Opixlig preset 'nonexistent' is not defined.");

it('throws when only width is provided without height', function (): void {
    Blade::render('<x-opixlig::image src="public/images/example.jpg" width="800" />');
})->throws(ViewException::class, 'Opixlig requires both width and height to be set together, or neither.');

it('throws when only height is provided without width', function (): void {
    Blade::render('<x-opixlig::image src="public/images/example.jpg" height="600" />');
})->throws(ViewException::class, 'Opixlig requires both width and height to be set together, or neither.');

it('passes through extra glide manipulations from preset', function (): void {
    config()->set('opixlig.presets.stylized', [
        'w' => 300,
        'h' => 200,
        'blur' => 10,
        'filt' => 'greyscale',
    ]);

    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" preset="stylized" />');

    expect($html)
        ->toContain('blur-10')
        ->toContain('filt-greyscale')
        ->toContain('width="300"');
});
