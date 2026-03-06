<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Opixlig\Services\ImageService;
use League\Glide\Signatures\SignatureFactory;

it('builds a signed URL with merged and sorted manipulations', function (): void {
    $baseManipulations = [
        'q' => 75,
        'fm' => 'webp',
        'fit' => 'crop',
    ];

    $urlManipulations = [
        'w' => 800,
        'q' => 60,
        'blur' => 10,
    ];

    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 1200,
        height: 800,
        baseManipulations: $baseManipulations,
    );

    $url = $service->url($urlManipulations);
    $path = parse_url($url, PHP_URL_PATH);
    $query = parse_url($url, PHP_URL_QUERY);

    parse_str(is_string($query) ? $query : '', $queryParams);

    $expectedManipulations = [
        'q' => 60,
        'fm' => 'webp',
        'fit' => 'crop',
        'w' => 800,
        'blur' => 10,
    ];

    expect($path)
        ->toBe('/images/public/images/hero.jpg/blur-10-fit-crop-fm-webp-q-60-w-800/hero.webp');

    $expectedSignature = SignatureFactory::create(Config::get('app.key'))
        ->addSignature((string) $path, $expectedManipulations)['s'];

    expect($queryParams)
        ->toHaveKey('s', $expectedSignature);
});

it('handles URL-style src values via parse_url in the constructor', function (): void {
    $service = new ImageService(
        src: 'https://cdn.example.com/public/gallery/photo.jpeg?token=abc123',
        width: 640,
        height: 480,
        baseManipulations: ['fm' => 'avif'],
    );

    $url = $service->url(['w' => 640]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)
        ->toBe('/images/public/gallery/photo.jpeg/fm-avif-w-640/photo.avif');
});

it('returns an empty placeholder for empty type', function (): void {
    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 1200,
        height: 800,
        baseManipulations: [],
    );

    expect($service->placeholder('empty'))->toBe('');
});

it('returns blur placeholder CSS when blur type is requested', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required to create an image fixture.');
    }

    $resource = imagecreatetruecolor(2, 2);
    $red = imagecolorallocate($resource, 255, 0, 0);
    imagefill($resource, 0, 0, $red);

    ob_start();
    imagepng($resource);
    $image = (string) ob_get_clean();
    imagedestroy($resource);

    Storage::disk('public')->put('opixlig-tests/sample.png', $image);

    $service = new ImageService(
        src: 'public/opixlig-tests/sample.png',
        width: 400,
        height: 300,
        baseManipulations: [],
    );

    $placeholder = $service->placeholder('blur');

    expect($placeholder)
        ->toStartWith('background-image:url("data:image/svg+xml;charset=utf-8,')
        ->toContain('background-size:cover;')
        ->toContain('background-position:center;')
        ->toContain('background-repeat:no-repeat;');
});

it('falls back to file extension when fm is not in manipulations', function (): void {
    $service = new ImageService(
        src: 'public/images/photo.png',
        width: 800,
        height: 600,
        baseManipulations: ['q' => 80],
    );

    $url = $service->url(['w' => 400]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->toContain('/photo.png');
});

it('uses fm extension for filename when fm is in manipulations', function (): void {
    $service = new ImageService(
        src: 'public/images/photo.png',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp'],
    );

    $url = $service->url(['w' => 400]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->toContain('/photo.webp');
});

it('allows url manipulations to override base manipulations', function (): void {
    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $url = $service->url(['fm' => 'avif', 'w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)
        ->toContain('fm-avif')
        ->toContain('/hero.avif')
        ->not->toContain('fm-webp');
});
