@props([
    'src' => '',
    'sizes' => '',
    'width' => '',
    'height' => '',
    'loading' => 'lazy',
    'decoding' => 'async', // async, sync, auto
    'quality' => config('opixlig.default_quality'),
    'placeholder' => config('opixlig.default_placeholder'), // empty or blur
    'format' => config('opixlig.default_format'), // webp, avif, png, jpg, gif or heic.
    'fit' => '', // contain, max, fill, fill-max, stretch, crop, crop-center, crop-top, crop-50-50-1, etc.
    'manipulations' => [], // Additional Glide manipulations. E.g. ['blur' => 50, 'filt' => 'greyscale'].
])

@php
    $defaultWidths = config('opixlig.default_widths');

    $baseManipulations = array_filter([
        'fm' => $format,
        'q' => $quality,
        'fit' => $fit,
    ], fn ($value) => $value !== '' && $value !== null);

    $baseManipulations = array_merge($baseManipulations, $manipulations);

    $imgService = img($src, $width, $height, $baseManipulations);

    if ($sizes) {
        $srcSet = collect($defaultWidths)->map(fn($w) => $imgService->url(['w' => $w]) . " {$w}w")->implode(', ');

        $finalSrc = $imgService->url(['w' => max($defaultWidths)]);
        $attrs = [
            'srcset' => $srcSet,
            'src' => $finalSrc,
            'sizes' => $sizes,
        ];
    } elseif ($width) {
        $dprWidths = [
            1 => $width,
            2 => $width * 2,
        ];

        $srcSet = collect($dprWidths)->map(fn($w, $dpr) => $imgService->url(['w' => $w]) . " {$dpr}x")->implode(', ');

        $finalSrc = $imgService->url(['w' => $width * 2]);
        $attrs = [
            'src' => $finalSrc,
            'srcset' => $srcSet,
            'width' => $width,
            'height' => $height,
        ];
    } else {
        $finalSrc = $imgService->url(['fm' => $format]);
        $attrs = ['src' => $finalSrc];
    }

    $placeholderCss = $imgService->placeholder($placeholder);
    if ($placeholderCss) {
        $attrs['style'] = $placeholderCss;
    }

@endphp

<img {{ $attributes->merge($attrs) }} loading="{{ $loading }}" decoding="{{ $decoding }}"
    width="{{ $width }}" height="{{ $height }}">
