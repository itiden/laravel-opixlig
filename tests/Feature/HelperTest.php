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
