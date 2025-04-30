<?php

namespace Itiden\Opixlig\Services;

use Illuminate\Support\Facades\Config;
use League\Glide\Signatures\SignatureFactory;

final class ImageService
{
    public function __construct(private string $src, private int $width, private int $height, private array $baseManipulations) {}

    public function url(array $manipulations): string
    {
        $signKey = Config::get('app.key');
        $src = $this->src;
        $manipulations = array_merge($this->baseManipulations, $manipulations);

        $filename = pathinfo($src, PATHINFO_FILENAME);
        $extension = $manipulations['fm'] ?? pathinfo($src, PATHINFO_EXTENSION);
        $filenameWithExtension = "{$filename}.{$extension}";

        $manipString = $this->stringifyManipulations($manipulations);
        $trimmedSrc = ltrim($src, '/');
        $publicFolder = Config::get('opixlig.public_folder');
        $path = "$publicFolder/$trimmedSrc/{$manipString}/{$filenameWithExtension}";

        $signature = SignatureFactory::create($signKey);
        $signature = $signature->addSignature("/{$path}", $manipulations)['s'];

        return url("/{$path}?s={$signature}");
    }

    public function placeholder(string $type = 'empty'): string
    {
        if ($type === 'empty') {
            return '';
        }

        $svg = (new Placeholder(
            src: $this->src,
            width: $this->width,
            height: $this->height,
        ))->generate();

        $css = "background-image:{$svg};background-size:cover;background-position:center;background-repeat:no-repeat;";

        return $css;
    }

    private function stringifyManipulations(array $manipulations): string
    {
        ksort($manipulations);

        return collect($manipulations)
            ->map(fn ($value, $key) => "{$key}-{$value}")
            ->implode('-');
    }

    private function parseManipulationsString(string $manipulations): array
    {
        $manipulations = collect(explode('-', $manipulations))
            ->chunk(2)
            ->mapWithKeys(function ($pair) {
                [$key, $value] = $pair->values();

                return [$key => $value];
            })
            ->toArray();

        ksort($manipulations);

        return $manipulations;
    }
}
