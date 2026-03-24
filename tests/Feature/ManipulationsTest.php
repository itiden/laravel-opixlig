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

it('normalizes friendly aliases to glide keys', function (): void {
    $result = Manipulations::normalize([
        'width' => 800,
        'height' => 600,
        'format' => 'webp',
        'quality' => 75,
    ]);

    expect($result)->toBe([
        'w' => 800,
        'h' => 600,
        'fm' => 'webp',
        'q' => 75,
    ]);
});

it('passes through keys already in glide form', function (): void {
    $result = Manipulations::normalize([
        'w' => 800,
        'h' => 600,
        'fm' => 'avif',
        'q' => 90,
    ]);

    expect($result)->toBe([
        'w' => 800,
        'h' => 600,
        'fm' => 'avif',
        'q' => 90,
    ]);
});

it('passes through unknown keys unchanged', function (): void {
    $result = Manipulations::normalize([
        'width' => 400,
        'blur' => 10,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
        'widths' => [384, 640],
    ]);

    expect($result)->toBe([
        'w' => 400,
        'blur' => 10,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
        'widths' => [384, 640],
    ]);
});

it('normalizes mixed alias and glide keys', function (): void {
    $result = Manipulations::normalize([
        'width' => 800,
        'fm' => 'webp',
        'quality' => 75,
        'fit' => 'crop-center',
    ]);

    expect($result)->toBe([
        'w' => 800,
        'fm' => 'webp',
        'q' => 75,
        'fit' => 'crop-center',
    ]);
});

it('normalizes an empty array', function (): void {
    expect(Manipulations::normalize([]))->toBe([]);
});

it('resolves empty preset with no overrides using config defaults', function (): void {
    $result = Manipulations::resolve('');

    expect($result)->toBe([
        'w' => 0,
        'h' => 0,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828, 1200, 1920, 2048, 3840],
        'manipulations' => [
            'fm' => 'webp',
            'q' => 75,
        ],
    ]);
});

it('resolves valid preset using glide keys', function (): void {
    config()->set('opixlig.presets.hero', [
        'w' => 1200,
        'h' => 675,
        'fm' => 'avif',
        'q' => 82,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
        'widths' => [640, 1200],
    ]);

    $result = Manipulations::resolve('hero');

    expect($result)->toBe([
        'w' => 1200,
        'h' => 675,
        'fm' => 'avif',
        'q' => 82,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
        'widths' => [640, 1200],
        'manipulations' => [
            'fm' => 'avif',
            'q' => 82,
            'fit' => 'crop-center',
        ],
    ]);
});

it('resolves valid preset using friendly aliases', function (): void {
    config()->set('opixlig.presets.aliases', [
        'width' => 900,
        'height' => 500,
        'format' => 'jpg',
        'quality' => 60,
        'fit' => 'contain',
        'placeholder' => 'blur',
    ]);

    $result = Manipulations::resolve('aliases');

    expect($result)->toBe([
        'w' => 900,
        'h' => 500,
        'fm' => 'jpg',
        'q' => 60,
        'fit' => 'contain',
        'placeholder' => 'blur',
        'widths' => [384, 640, 828, 1200, 1920, 2048, 3840],
        'manipulations' => [
            'fm' => 'jpg',
            'q' => 60,
            'fit' => 'contain',
        ],
    ]);
});

it('resolves by merging preset and overrides with overrides winning', function (): void {
    config()->set('opixlig.presets.merge', [
        'w' => 1200,
        'h' => 600,
        'fm' => 'webp',
        'q' => 75,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
        'blur' => 5,
        'filt' => 'greyscale',
    ]);

    $result = Manipulations::resolve('merge', [
        'w' => 800,
        'quality' => 90,
        'format' => 'avif',
        'fit' => 'contain',
        'placeholder' => 'empty',
        'blur' => 10,
    ]);

    expect($result)->toBe([
        'w' => 800,
        'h' => 600,
        'fm' => 'avif',
        'q' => 90,
        'fit' => 'contain',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828, 1200, 1920, 2048, 3840],
        'manipulations' => [
            'blur' => 10,
            'filt' => 'greyscale',
            'fm' => 'avif',
            'q' => 90,
            'fit' => 'contain',
        ],
    ]);
});

it('falls back to config defaults when preset and overrides are missing values', function (): void {
    config()->set('opixlig.presets.partial', [
        'w' => 333,
        'h' => 222,
    ]);

    $result = Manipulations::resolve('partial');

    expect($result)->toBe([
        'w' => 333,
        'h' => 222,
        'fm' => 'webp',
        'q' => 75,
        'fit' => '',
        'placeholder' => 'empty',
        'widths' => [384, 640, 828, 1200, 1920, 2048, 3840],
        'manipulations' => [
            'fm' => 'webp',
            'q' => 75,
        ],
    ]);
});

it('throws for undefined preset', function (): void {
    expect(fn () => Manipulations::resolve('nonexistent'))
        ->toThrow(InvalidArgumentException::class);
});

it('keeps extra glide manipulations from preset in manipulations key', function (): void {
    config()->set('opixlig.presets.effects', [
        'w' => 500,
        'h' => 500,
        'blur' => 12,
        'filt' => 'sepia',
        'bri' => 15,
    ]);

    $result = Manipulations::resolve('effects');

    expect($result['manipulations'])->toBe([
        'blur' => 12,
        'filt' => 'sepia',
        'bri' => 15,
        'fm' => 'webp',
        'q' => 75,
    ]);
});

it('uses preset widths instead of config defaults', function (): void {
    config()->set('opixlig.presets.custom-widths', [
        'widths' => [320, 768, 1024],
    ]);

    $result = Manipulations::resolve('custom-widths');

    expect($result['widths'])->toBe([320, 768, 1024]);
});

it('ignores null overrides and keeps preset values', function (): void {
    config()->set('opixlig.presets.null-overrides', [
        'w' => 1200,
        'h' => 800,
        'fm' => 'avif',
        'q' => 88,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
    ]);

    $result = Manipulations::resolve('null-overrides', [
        'w' => null,
        'h' => null,
        'fm' => null,
        'q' => null,
        'fit' => null,
        'placeholder' => null,
    ]);

    expect($result)->toBe([
        'w' => 1200,
        'h' => 800,
        'fm' => 'avif',
        'q' => 88,
        'fit' => 'crop-center',
        'placeholder' => 'blur',
        'widths' => [384, 640, 828, 1200, 1920, 2048, 3840],
        'manipulations' => [
            'fm' => 'avif',
            'q' => 88,
            'fit' => 'crop-center',
        ],
    ]);
});

it('throws when only width is provided without height', function (): void {
    expect(fn () => Manipulations::resolve('', ['w' => 800]))
        ->toThrow(InvalidArgumentException::class, 'both width and height');
});

it('throws when only height is provided without width', function (): void {
    expect(fn () => Manipulations::resolve('', ['h' => 600]))
        ->toThrow(InvalidArgumentException::class, 'both width and height');
});

it('does not throw when both width and height are zero', function (): void {
    $result = Manipulations::resolve('');

    expect($result['w'])->toBe(0)
        ->and($result['h'])->toBe(0);
});

it('does not throw when both width and height are provided', function (): void {
    $result = Manipulations::resolve('', ['w' => 800, 'h' => 600]);

    expect($result['w'])->toBe(800)
        ->and($result['h'])->toBe(600);
});

it('throws when widths is an empty array', function (): void {
    expect(fn () => Manipulations::resolve('', ['widths' => []]))
        ->toThrow(InvalidArgumentException::class, 'non-empty list');
});

it('throws when widths contains a non-integer value', function (): void {
    expect(fn () => Manipulations::resolve('', ['widths' => [320, 'large', 1024]]))
        ->toThrow(InvalidArgumentException::class, 'non-empty list of positive integers');
});

it('throws when widths contains a zero value', function (): void {
    expect(fn () => Manipulations::resolve('', ['widths' => [0, 640]]))
        ->toThrow(InvalidArgumentException::class, 'non-empty list of positive integers');
});

it('throws when widths contains a negative value', function (): void {
    expect(fn () => Manipulations::resolve('', ['widths' => [-100, 640]]))
        ->toThrow(InvalidArgumentException::class, 'non-empty list of positive integers');
});

it('allows inline widths override to take precedence over preset widths', function (): void {
    config()->set('opixlig.presets.wide', [
        'w' => 1200,
        'h' => 600,
        'widths' => [600, 1200],
    ]);

    $result = Manipulations::resolve('wide', ['widths' => [300, 600]]);

    expect($result['widths'])->toBe([300, 600]);
});

it('allows inline widths override to take precedence over config default widths', function (): void {
    $result = Manipulations::resolve('', ['widths' => [320, 768]]);

    expect($result['widths'])->toBe([320, 768]);
});
