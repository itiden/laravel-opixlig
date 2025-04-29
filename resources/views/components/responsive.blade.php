@props([
    'src' => '',
    'sizes' => '',
    'width' => '',
    'height' => '',
    'loading' => 'lazy',
    'decoding' => 'async', // async, sync, auto
    'quality' => config('image.default_quality'),
    'placeholder' => config('image.default_placeholder'), // empty or blur
])

@php
    $defaultWidths = config('image.default_widths');

    $imgService = img($src, $width, $height, ['fm' => 'webp', 'q' => $quality]);

    if ($sizes) {
        $srcSet = collect($defaultWidths)->map(fn($w) => $imgService->url(['w' => $w]) . " {$w}w")->implode(', ');

        $finalSrc = $imgService->url(['w' => max($defaultWidths)]);
        $attrs = [
            'srcset' => $srcSet,
            'src' => $finalSrc,
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
        $finalSrc = $imgService->url(['fm' => 'webp']);
        $attrs = ['src' => $finalSrc];
    }

    $base64 = $placeholder === 'blur' ? $imgService->placeholder() : null;
    if ($base64) {
        $attrs[
            'style'
        ] = "background-image:{$base64};background-size:cover;background-position:center;background-repeat:no-repeat;";
    }

@endphp

<img {{ $attributes->merge($attrs) }} sizes="{{ $sizes }}" loading="{{ $loading }}"
    decoding="{{ $decoding }}" width="{{ $width }}" height="{{ $height }}">
