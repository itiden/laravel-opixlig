<?php

use Itiden\Opixlig\Utils\Manipulations;

it('stringifies simple manipulations sorted by key', function (): void {
    $result = Manipulations::stringify([
        'w' => 800,
        'fm' => 'webp',
        'q' => 75,
    ]);

    expect($result)->toBe('fm-webp_q-75_w-800');
});

it('stringifies manipulations with dashes in values', function (): void {
    $result = Manipulations::stringify([
        'fit' => 'crop-center',
        'fm' => 'webp',
        'q' => 75,
    ]);

    expect($result)->toBe('fit-crop-center_fm-webp_q-75');
});

it('stringifies focal point fit values correctly', function (): void {
    $result = Manipulations::stringify([
        'fit' => 'crop-44-13-1',
        'fm' => 'webp',
        'w' => 800,
    ]);

    expect($result)->toBe('fit-crop-44-13-1_fm-webp_w-800');
});

it('parses a simple manipulation string', function (): void {
    $result = Manipulations::parse('fm-webp_q-75_w-800');

    expect($result)->toBe([
        'fm' => 'webp',
        'q' => '75',
        'w' => '800',
    ]);
});

it('parses manipulation string with dashes in values', function (): void {
    $result = Manipulations::parse('fit-crop-center_fm-webp_q-75');

    expect($result)->toBe([
        'fit' => 'crop-center',
        'fm' => 'webp',
        'q' => '75',
    ]);
});

it('parses focal point fit values correctly', function (): void {
    $result = Manipulations::parse('fit-crop-44-13-1_fm-webp_w-800');

    expect($result)->toBe([
        'fit' => 'crop-44-13-1',
        'fm' => 'webp',
        'w' => '800',
    ]);
});

it('roundtrips stringify then parse', function (): void {
    $original = [
        'fit' => 'crop-bottom-right',
        'fm' => 'avif',
        'q' => 90,
        'w' => 1200,
    ];

    $string = Manipulations::stringify($original);
    $parsed = Manipulations::parse($string);

    expect($parsed)->toBe([
        'fit' => 'crop-bottom-right',
        'fm' => 'avif',
        'q' => '90',
        'w' => '1200',
    ]);
});

it('parses fill-max fit value correctly', function (): void {
    $result = Manipulations::parse('fit-fill-max_fm-webp');

    expect($result)->toBe([
        'fit' => 'fill-max',
        'fm' => 'webp',
    ]);
});
