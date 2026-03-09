@props([
    'src' => '',
    'preset' => '',
    'sizes' => '',
    'width' => '',
    'height' => '',
    'loading' => 'lazy',
    'decoding' => 'async',
    'quality' => null,
    'placeholder' => null,
    'format' => null,
    'fit' => '',
    'manipulations' => [],
])

@php
    use Itiden\Opixlig\Utils\ImageAttributes;
    use Itiden\Opixlig\Utils\Manipulations;

    $resolved = Manipulations::resolve($preset, array_filter([
        'w' => $width !== '' ? (int) $width : null,
        'h' => $height !== '' ? (int) $height : null,
        'fm' => $format,
        'q' => $quality,
        'fit' => $fit !== '' ? $fit : null,
        'placeholder' => $placeholder,
        ...$manipulations,
    ], static fn ($value) => $value !== null));

    $imgService = img($src, $resolved['w'], $resolved['h'], $resolved['manipulations']);
    $attrs = ImageAttributes::from($imgService, $resolved, $sizes);

    $width = $resolved['w'];
    $height = $resolved['h'];
@endphp

<img {{ $attributes->merge($attrs) }} loading="{{ $loading }}" decoding="{{ $decoding }}"
    width="{{ $width }}" height="{{ $height }}">
