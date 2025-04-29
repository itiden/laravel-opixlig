@props([
    'src' => '',
    'width' => '',
    'height' => '',
    'loading' => 'lazy',
    'decoding' => 'async', // async, sync, auto
    'quality' => config('image.default_quality', 75),
    'placeholder' => config('image.default_placeholder', 'empty'), // empty or blur
    'params' => [],
])

@php
    $imgService = img($src, $width, $height, array_map(['fm' => 'webp', 'q' => $quality], $params));

    $attrs = [
        'src' => $imgService->url($params),
        'width' => $width,
        'height' => $height,
    ];

    $base64 = $placeholder === 'blur' ? $imgService->placeholder() : null;
    if ($base64) {
        $attrs[
            'style'
        ] = "background-image:{$base64};background-size:cover;background-position:center;background-repeat:no-repeat;";
    }
@endphp

<img {{ $attributes->merge($attrs) }} sizes="{{ $sizes }}" loading="{{ $loading }}"
    decoding="{{ $decoding }}" width="{{ $width }}" height="{{ $height }}">
