<?php

use Illuminate\Support\Facades\Blade;

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
