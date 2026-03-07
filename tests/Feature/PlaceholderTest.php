<?php

use Illuminate\Support\Facades\Storage;
use Itiden\Opixlig\Services\Placeholder;

beforeEach(function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required.');
    }

    $resource = imagecreatetruecolor(10, 10);
    $color = imagecolorallocate($resource, 100, 150, 200);
    imagefill($resource, 0, 0, $color);

    ob_start();
    imagepng($resource);
    $image = (string) ob_get_clean();
    imagedestroy($resource);

    Storage::disk('public')->put('placeholder-tests/sample.png', $image);
});

afterEach(function (): void {
    Storage::disk('public')->deleteDirectory('placeholder-tests');
});

it('generates a blur placeholder SVG data URL', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 400,
        height: 300,
    );

    $result = $placeholder->generate();

    expect($result)
        ->toStartWith('url("data:image/svg+xml;charset=utf-8,')
        ->toContain('feGaussianBlur')
        ->toContain('lqip-b')
        ->toEndWith('")');
});

it('includes viewBox dimensions in the SVG', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 800,
        height: 600,
    );

    $result = rawurldecode($placeholder->generate());

    expect($result)
        ->toContain("viewBox='0 0 800 600'");
});

it('sets preserveAspectRatio to none when viewBox is present', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 200,
        height: 100,
    );

    $result = rawurldecode($placeholder->generate());

    expect($result)
        ->toContain("preserveAspectRatio='none'");
});

it('includes a base64 image reference in the SVG', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 100,
        height: 100,
    );

    $result = rawurldecode($placeholder->generate());

    expect($result)
        ->toContain('href=')
        ->toContain('data:image/jpeg;base64');
});

it('returns contain preserveAspectRatio when objectFit is contain and no viewBox', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 400,
        height: 300,
    );

    $method = new ReflectionMethod($placeholder, 'getImageBlurSvg');

    $result = rawurldecode((string) $method->invoke(
        $placeholder,
        null,  // width
        null,  // height
        null,  // blurWidth
        null,  // blurHeight
        'data:image/jpeg;base64,test',
        'contain',  // objectFit
    ));

    expect($result)->toContain("preserveAspectRatio='xMidYMid'");
});

it('returns cover preserveAspectRatio when objectFit is cover and no viewBox', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 400,
        height: 300,
    );

    $method = new ReflectionMethod($placeholder, 'getImageBlurSvg');

    $result = rawurldecode((string) $method->invoke(
        $placeholder,
        null,  // width
        null,  // height
        null,  // blurWidth
        null,  // blurHeight
        'data:image/jpeg;base64,test',
        'cover',  // objectFit
    ));

    expect($result)->toContain("preserveAspectRatio='xMidYMid slice'");
});

it('returns none preserveAspectRatio when objectFit is unrecognized and no viewBox', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 400,
        height: 300,
    );

    $method = new ReflectionMethod($placeholder, 'getImageBlurSvg');

    $result = rawurldecode((string) $method->invoke(
        $placeholder,
        null,  // width
        null,  // height
        null,  // blurWidth
        null,  // blurHeight
        'data:image/jpeg;base64,test',
        'fill',  // objectFit - not contain or cover
    ));

    expect($result)->toContain("preserveAspectRatio='none'");
});

it('uses blurWidth and blurHeight for viewBox when provided', function (): void {
    $placeholder = new Placeholder(
        src: 'public/placeholder-tests/sample.png',
        width: 400,
        height: 300,
    );

    $method = new ReflectionMethod($placeholder, 'getImageBlurSvg');

    $result = rawurldecode((string) $method->invoke(
        $placeholder,
        200,   // width
        100,   // height
        8,     // blurWidth - becomes 8*40 = 320
        6,     // blurHeight - becomes 6*40 = 240
        'data:image/jpeg;base64,test',
        null,  // objectFit
    ));

    expect($result)->toContain("viewBox='0 0 320 240'");
});
