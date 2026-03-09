<?php

namespace Itiden\Opixlig\Utils;

use Itiden\Opixlig\Services\ImageService;

final class ImageAttributes
{
    /**
     * @param  array{w: int, h: int, fm: string, q: int|string, fit: string, placeholder: string, widths: list<int>, manipulations: array<string, string|int>}  $resolved
     * @return array<string, string|int>
     */
    public static function from(ImageService $service, array $resolved, string $sizes = ''): array
    {
        $width = $resolved['w'];
        $height = $resolved['h'];

        if ($sizes !== '' && $width !== 0 && $height !== 0) {
            return self::responsiveAttrs($service, $resolved, $sizes);
        }

        if ($width !== 0 && $height !== 0) {
            return self::fixedAttrs($service, $resolved);
        }

        return self::fallbackAttrs($service, $resolved);
    }

    /**
     * @param  array{w: int, h: int, fm: string, q: int|string, fit: string, placeholder: string, widths: list<int>, manipulations: array<string, string|int>}  $resolved
     * @return array<string, string|int>
     */
    private static function responsiveAttrs(ImageService $service, array $resolved, string $sizes): array
    {
        $width = $resolved['w'];
        $height = $resolved['h'];
        $widths = $resolved['widths'];

        $srcSet = collect($widths)
            ->map(static fn (int $w): string => $service->url(['w' => $w, 'h' => intval(round($w * $height / $width))])." {$w}w")
            ->implode(', ');

        $maxWidth = max($widths);
        $finalSrc = $service->url(['w' => $maxWidth, 'h' => intval(round($maxWidth * $height / $width))]);

        $attrs = [
            'srcset' => $srcSet,
            'src' => $finalSrc,
            'sizes' => $sizes,
        ];

        return self::withPlaceholder($service, $resolved, $attrs);
    }

    /**
     * @param  array{w: int, h: int, fm: string, q: int|string, fit: string, placeholder: string, widths: list<int>, manipulations: array<string, string|int>}  $resolved
     * @return array<string, string|int>
     */
    private static function fixedAttrs(ImageService $service, array $resolved): array
    {
        $width = $resolved['w'];
        $height = $resolved['h'];

        $dprWidths = [
            1 => $width,
            2 => $width * 2,
        ];

        $srcSet = collect($dprWidths)
            ->map(static fn (int $w, int $dpr): string => $service->url(['w' => $w, 'h' => $height * $dpr])." {$dpr}x")
            ->implode(', ');

        $finalSrc = $service->url(['w' => $width * 2, 'h' => $height * 2]);

        $attrs = [
            'src' => $finalSrc,
            'srcset' => $srcSet,
            'width' => $width,
            'height' => $height,
        ];

        return self::withPlaceholder($service, $resolved, $attrs);
    }

    /**
     * @param  array{w: int, h: int, fm: string, q: int|string, fit: string, placeholder: string, widths: list<int>, manipulations: array<string, string|int>}  $resolved
     * @return array<string, string|int>
     */
    private static function fallbackAttrs(ImageService $service, array $resolved): array
    {
        $attrs = [
            'src' => $service->url(['fm' => $resolved['fm']]),
        ];

        return self::withPlaceholder($service, $resolved, $attrs);
    }

    /**
     * @param  array{w: int, h: int, fm: string, q: int|string, fit: string, placeholder: string, widths: list<int>, manipulations: array<string, string|int>}  $resolved
     * @param  array<string, string|int>  $attrs
     * @return array<string, string|int>
     */
    private static function withPlaceholder(ImageService $service, array $resolved, array $attrs): array
    {
        $placeholderCss = $service->placeholder($resolved['placeholder']);

        if ($placeholderCss !== '') {
            $attrs['style'] = $placeholderCss;
        }

        return $attrs;
    }
}
