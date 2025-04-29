<?php

namespace Itiden\LaravelImage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;

final class ImageController
{
    public function __invoke(Request $request, string $fullpath, string $manipulations, string $filename)
    {
        $signKey = Config::get('app.key');

        $manipArray = collect(explode('-', $manipulations))
            ->chunk(2)
            ->mapWithKeys(function ($pair) {
                [$key, $value] = $pair->values();

                return [$key => $value];
            })
            ->toArray();
        ksort($manipArray);

        SignatureFactory::create($signKey)->validateRequest(
            $request->path(),
            array_merge($manipArray, ['s' => $request->query('s')])
        );
        $publicFolder = Config::get('image.public_folder');
        $storageFolder = Config::get('image.storage_folder');

        [$container, $path] = explode('/', ltrim($fullpath, '/'), 2);
        $inputPath = Storage::disk($container)->path($path);
        $cacheFolder = dirname($fullpath);
        $cacheDir = storage_path("$storageFolder/$cacheFolder");
        $originalFilename = pathinfo($inputPath, PATHINFO_BASENAME);
        $outputFolder = public_path("$publicFolder/$fullpath/$manipulations");

        $glideServer = ServerFactory::create([
            'source' => dirname($inputPath),
            'cache' => $cacheDir,
            'driver' => Config::get('image.driver'),
        ]);

        File::ensureDirectoryExists($outputFolder);

        $conversionResult = $cacheDir.'/'.$glideServer->makeImage($originalFilename, $manipArray);
        rename($conversionResult, "$outputFolder/$filename");

        return response()->file("$outputFolder/$filename");
    }
}
