<?php

use Illuminate\Support\Facades\Storage;
use Itiden\Opixlig\Services\ImageService;
use Itiden\Opixlig\Utils\ImageAttributes;

it('builds responsive attrs with width descriptors, sizes, and aspect-ratio heights', function (): void {
    $service = new ImageService(
        src: 'public/images/test.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $resolved = [
        'w' => 800,
        'h' => 600,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828],
        'manipulations' => ['fm' => 'webp', 'q' => 75],
    ];

    $sizes = '(max-width: 768px) 100vw, 50vw';
    $attrs = ImageAttributes::from($service, $resolved, $sizes);

    $expectedSrcSet = implode(', ', [
        $service->url(['w' => 384, 'h' => 288]).' 384w',
        $service->url(['w' => 640, 'h' => 480]).' 640w',
        $service->url(['w' => 828, 'h' => 621]).' 828w',
    ]);

    expect($attrs)
        ->toHaveKey('srcset', $expectedSrcSet)
        ->toHaveKey('sizes', $sizes)
        ->toHaveKey('src', $service->url(['w' => 828, 'h' => 621]))
        ->not->toHaveKey('width')
        ->not->toHaveKey('height');
});

it('builds fixed attrs with 1x and 2x descriptors and explicit dimensions', function (): void {
    $service = new ImageService(
        src: 'public/images/test.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $resolved = [
        'w' => 800,
        'h' => 600,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828],
        'manipulations' => ['fm' => 'webp', 'q' => 75],
    ];

    $attrs = ImageAttributes::from($service, $resolved);

    $expectedSrcSet = implode(', ', [
        $service->url(['w' => 800, 'h' => 600]).' 1x',
        $service->url(['w' => 1600, 'h' => 1200]).' 2x',
    ]);

    expect($attrs)
        ->toHaveKey('srcset', $expectedSrcSet)
        ->toHaveKey('src', $service->url(['w' => 1600, 'h' => 1200]))
        ->toHaveKey('width', 800)
        ->toHaveKey('height', 600)
        ->not->toHaveKey('sizes');
});

it('builds fallback attrs with src only when width or height is zero', function (): void {
    $service = new ImageService(
        src: 'public/images/test.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $resolved = [
        'w' => 0,
        'h' => 600,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828],
        'manipulations' => ['fm' => 'webp', 'q' => 75],
    ];

    $attrs = ImageAttributes::from($service, $resolved);

    expect($attrs)
        ->toHaveKey('src', $service->url(['fm' => 'webp']))
        ->not->toHaveKey('srcset')
        ->not->toHaveKey('sizes')
        ->not->toHaveKey('width')
        ->not->toHaveKey('height')
        ->not->toHaveKey('style');

    expect($attrs['src'])->toContain('fm-webp');
});

it('does not add style when placeholder is empty', function (): void {
    $service = new ImageService(
        src: 'public/images/test.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $resolved = [
        'w' => 800,
        'h' => 600,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828],
        'manipulations' => ['fm' => 'webp', 'q' => 75],
    ];

    $attrs = ImageAttributes::from($service, $resolved);

    expect($attrs)->not->toHaveKey('style');
});

it('adds blur placeholder style when placeholder is blur', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required.');
    }

    $resource = imagecreatetruecolor(10, 10);
    $color = imagecolorallocate($resource, 200, 100, 50);
    imagefill($resource, 0, 0, $color);

    ob_start();
    imagepng($resource);
    $image = (string) ob_get_clean();
    Storage::disk('public')->put('image-attributes/photo.png', $image);

    $service = new ImageService(
        src: 'public/image-attributes/photo.png',
        width: 200,
        height: 100,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $resolved = [
        'w' => 200,
        'h' => 100,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'blur',
        'widths' => [384, 640, 828],
        'manipulations' => ['fm' => 'webp', 'q' => 75],
    ];

    $attrs = ImageAttributes::from($service, $resolved);

    expect($attrs)
        ->toHaveKey('style')
        ->and($attrs['style'])
        ->toContain('background-image:url(')
        ->toContain('background-size:cover;')
        ->toContain('background-position:center;')
        ->toContain('background-repeat:no-repeat;');

    Storage::disk('public')->deleteDirectory('image-attributes');
});
