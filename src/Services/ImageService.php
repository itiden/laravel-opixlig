<?php

namespace Itiden\Opixlig\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Itiden\Opixlig\Utils\Manipulations;
use League\Glide\Signatures\SignatureFactory;

final class ImageService
{
    private readonly ?string $resolvedVersion;

    /** @param array<string, string|int> $baseManipulations */
    public function __construct(private string $src, private readonly int $width, private readonly int $height, private readonly array $baseManipulations, ?string $version = null)
    {
        $path = parse_url($src, PHP_URL_PATH);
        $this->src = is_string($path) ? $path : $src;
        $this->resolvedVersion = $this->resolveVersion($version);
    }

    /** @param array<string, string|int> $manipulations */
    public function url(array $manipulations): string
    {
        /** @var string $signKey */
        $signKey = Config::get('app.key');
        $src = $this->src;
        $manipulations = array_merge($this->baseManipulations, $manipulations);

        if ($this->resolvedVersion !== null) {
            $manipulations['v'] = $this->resolvedVersion;
        }

        $filename = pathinfo($src, PATHINFO_FILENAME);
        $extension = $manipulations['fm'] ?? pathinfo($src, PATHINFO_EXTENSION);
        $filenameWithExtension = "{$filename}.{$extension}";

        $manipString = Manipulations::stringify($manipulations);
        $trimmedSrc = ltrim($src, '/');
        /** @var string $publicFolder */
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

        return "background-image:{$svg};background-size:cover;background-position:center;background-repeat:no-repeat;";
    }

    private function resolveVersion(?string $explicit): ?string
    {
        if ($explicit !== null) {
            $slugged = Str::slug($explicit);

            return $slugged !== '' ? $slugged : null;
        }

        /** @var string|null $strategy */
        $strategy = Config::get('opixlig.version');

        if ($strategy === 'mtime') {
            return $this->getMtimeVersion();
        }

        return null;
    }

    private function getMtimeVersion(): ?string
    {
        $parts = explode('/', ltrim($this->src, '/'), 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$disk, $path] = $parts;

        try {
            $mtime = Storage::disk($disk)->lastModified($path);

            return (string) $mtime;
        } catch (\Throwable) {
            return null;
        }
    }
}
