<?php

namespace Itiden\Opixlig\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;

final class Placeholder
{
    public function __construct(private string $src, private int $width, private int $height) {}

    public function generate(): string
    {
        [$container, $path] = explode('/', ltrim($this->src, '/'), 2);
        $inputPath = Storage::disk($container)->path($path);
        $originalFilename = pathinfo($inputPath, PATHINFO_BASENAME);

        $glideServer = ServerFactory::create([
            'source' => dirname($inputPath),
            'cache' => Storage::path('framework/cache/images'),
            'driver' => Config::get('image.driver'),
        ]);

        $base64 = $glideServer->getImageAsBase64($originalFilename, [
            'w' => 30,
            'q' => 30,
            'fm' => 'jpg',
        ]);

        $svgString = $this->getImageBlurSvg(
            width: $this->width,
            height: $this->height,
            blurDataURL: $base64,
        );

        return "url(\"data:image/svg+xml;charset=utf-8,{$svgString}\")";
    }

    /**
     * Based on code from Next.js (MIT License).
     * https://github.com/vercel/next.js/blob/canary/packages/next/src/shared/lib/image-blur-svg.ts
     */
    private function getImageBlurSvg(
        ?int $width = null,
        ?int $height = null,
        ?int $blurWidth = null,
        ?int $blurHeight = null,
        string $blurDataURL = '',
        ?string $objectFit = null
    ): string {
        $blurValue = 50;
        $svgWidth = $blurWidth ? $blurWidth * 40 : $width;
        $svgHeight = $blurHeight ? $blurHeight * 40 : $height;

        $viewBox = ($svgWidth && $svgHeight) ? "viewBox='0 0 $svgWidth $svgHeight'" : '';

        $preserveAspectRatio = $viewBox
            ? 'none'
            : ($objectFit === 'contain'
                ? 'xMidYMid'
                : ($objectFit === 'cover'
                    ? 'xMidYMid slice'
                    : 'none'));

        $blurId = 'lqip-b';

        return rawurlencode(
            "<svg xmlns='http://www.w3.org/2000/svg' $viewBox><filter id='$blurId' color-interpolation-filters='sRGB'><feGaussianBlur stdDeviation='$blurValue'/><feColorMatrix values='1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 100 -1' result='s'/><feFlood x='0' y='0' width='100%' height='100%'/><feComposite operator='out' in='s'/><feComposite in2='SourceGraphic'/><feGaussianBlur stdDeviation='$blurValue'/></filter><image width='100%' height='100%' x='0' y='0' preserveAspectRatio='$preserveAspectRatio' style='filter: url(#$blurId);' href='$blurDataURL'/></svg>"
        );
    }
}
