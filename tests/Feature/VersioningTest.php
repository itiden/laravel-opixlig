<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Opixlig\Services\ImageService;

it('includes version in URL path when explicit version is provided', function (): void {
    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
        version: '2',
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->toContain('v-2');
});

it('does not include version in URL when version is null and config is null', function (): void {
    Config::set('opixlig.version', null);

    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->not->toContain('v-');
});

it('sanitizes version with Str::slug', function (): void {
    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp'],
        version: 'Hello World 2024',
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->toContain('v-hello-world-2024');
});

it('ignores version when slug resolves to empty string', function (): void {
    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp'],
        version: '!!!',
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->not->toContain('v-');
});

it('uses mtime strategy when config version is mtime', function (): void {
    Config::set('opixlig.version', 'mtime');

    Storage::fake('public');
    Storage::disk('public')->put('images/hero.jpg', 'fake-image-content');

    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp'],
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->toMatch('/v-\d+/');
});

it('explicit version overrides mtime config', function (): void {
    Config::set('opixlig.version', 'mtime');

    $service = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp'],
        version: '42',
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)
        ->toContain('v-42')
        ->not->toMatch('/v-\d{5,}/');
});

it('gracefully handles mtime failure for missing file', function (): void {
    Config::set('opixlig.version', 'mtime');

    Storage::fake('public');

    $service = new ImageService(
        src: 'public/images/nonexistent.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp'],
    );

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->not->toContain('v-');
});

it('version changes the URL path producing a different cache location', function (): void {
    $service1 = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
        version: '1',
    );

    $service2 = new ImageService(
        src: 'public/images/hero.jpg',
        width: 800,
        height: 600,
        baseManipulations: ['fm' => 'webp', 'q' => 75],
        version: '2',
    );

    $path1 = parse_url($service1->url(['w' => 800]), PHP_URL_PATH);
    $path2 = parse_url($service2->url(['w' => 800]), PHP_URL_PATH);

    expect($path1)->not->toBe($path2);
});

it('renders version in blade component via version prop', function (): void {
    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" version="3" />');

    expect($html)->toContain('v-3');
});

it('does not render version in blade component when version is not set', function (): void {
    Config::set('opixlig.version', null);

    $html = Blade::render('<x-opixlig::image src="public/images/example.jpg" width="320" height="180" />');

    expect($html)->not->toContain('v-');
});

it('passes version through the img helper function', function (): void {
    $service = img('public/images/hero.jpg', 800, 600, ['fm' => 'webp'], version: '5');

    $url = $service->url(['w' => 800]);
    $path = parse_url($url, PHP_URL_PATH);

    expect($path)->toContain('v-5');
});

it('version is included in signature validation', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required.');
    }

    $resource = imagecreatetruecolor(10, 10);
    $red = imagecolorallocate($resource, 255, 0, 0);
    imagefill($resource, 0, 0, $red);

    ob_start();
    imagejpeg($resource);
    $image = (string) ob_get_clean();
    imagedestroy($resource);

    Storage::disk('public')->put('images/versioned.jpg', $image);

    $service = img('public/images/versioned.jpg', 100, 100, ['fm' => 'jpg', 'q' => 75], version: '7');
    $url = $service->url(['w' => 100]);

    $response = $this->get(parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY));

    $response->assertOk();
});
