<?php

use Itiden\Opixlig\Services\ImageService;

it('returns an ImageService instance', function (): void {
    $service = img('public/images/hero.jpg', 800, 600, ['fm' => 'webp']);

    expect($service)->toBeInstanceOf(ImageService::class);
});

it('passes src, width, height and base manipulations to ImageService', function (): void {
    $service = img('/public/gallery/photo.jpg?cache=123', 320, 240, [
        'fm' => 'avif',
        'q' => 70,
    ]);

    $reflection = new ReflectionClass($service);

    $src = $reflection->getProperty('src');
    $width = $reflection->getProperty('width');
    $height = $reflection->getProperty('height');
    $baseManipulations = $reflection->getProperty('baseManipulations');

    expect($src->getValue($service))->toBe('/public/gallery/photo.jpg')
        ->and($width->getValue($service))->toBe(320)
        ->and($height->getValue($service))->toBe(240)
        ->and($baseManipulations->getValue($service))->toBe([
            'fm' => 'avif',
            'q' => 70,
        ]);
});

it('resolves preset and applies width, height and manipulations', function (): void {
    config()->set('opixlig.presets.avatar', [
        'width' => 64,
        'height' => 64,
        'quality' => 70,
        'fit' => 'crop-center',
        'format' => 'webp',
    ]);

    $service = img('public/images/hero.jpg', preset: 'avatar');

    $reflection = new ReflectionClass($service);

    expect($reflection->getProperty('width')->getValue($service))->toBe(64)
        ->and($reflection->getProperty('height')->getValue($service))->toBe(64)
        ->and($reflection->getProperty('baseManipulations')->getValue($service))->toBe([
            'fm' => 'webp',
            'q' => 70,
            'fit' => 'crop-center',
        ]);
});

it('allows explicit args to override preset in helper', function (): void {
    config()->set('opixlig.presets.avatar', [
        'w' => 64,
        'h' => 64,
        'q' => 70,
    ]);

    $service = img('public/images/hero.jpg', width: 128, height: 128, preset: 'avatar');

    $reflection = new ReflectionClass($service);

    expect($reflection->getProperty('width')->getValue($service))->toBe(128)
        ->and($reflection->getProperty('height')->getValue($service))->toBe(128);
});

it('throws exception for undefined preset in helper', function (): void {
    img('public/images/hero.jpg', preset: 'nonexistent');
})->throws(InvalidArgumentException::class, "Opixlig preset 'nonexistent' is not defined.");
