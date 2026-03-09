@props([
    'src' => '',
    'sizes' => '',
    'width' => '',
    'height' => '',
    'loading' => 'lazy',
    'decoding' => 'async', // async, sync, auto
    'quality' => config('opixlig.defaults.quality'),
    'placeholder' => config('opixlig.defaults.placeholder'), // empty or blur
    'format' => config('opixlig.defaults.format'), // webp, avif, png, jpg, gif or heic.
    'fit' => '', // contain, max, fill, fill-max, stretch, crop, crop-center, crop-top, crop-50-50-1, etc.
    'manipulations' => [], // Additional Glide manipulations. E.g. ['blur' => 50, 'filt' => 'greyscale'].
])

@php
    $defaultWidths = config('opixlig.defaults.widths');

    $baseManipulations = array_filter([
        'fm' => $format,
        'q' => $quality,
        'fit' => $fit,
    ], fn ($value) => $value !== '' && $value !== null);

    $baseManipulations = array_merge($baseManipulations, $manipulations);

    $imgService = img($src, (int) $width, (int) $height, $baseManipulations);

    if ($sizes) {
        $srcSet = collect($defaultWidths)->map(fn($w) => $imgService->url(['w' => $w, 'h' => intval(round($w * $height / $width))]) . " {$w}w")->implode(', ');

        $finalSrc = $imgService->url(['w' => max($defaultWidths), 'h' => intval(round(max($defaultWidths) * $height / $width))]);
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

        $srcSet = collect($dprWidths)->map(fn($w, $dpr) => $imgService->url(['w' => $w, 'h' => $height * $dpr]) . " {$dpr}x")->implode(', ');

        $finalSrc = $imgService->url(['w' => $width * 2, 'h' => $height * 2]);
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
