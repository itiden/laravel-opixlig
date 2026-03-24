<?php

namespace Itiden\Opixlig\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Opixlig\Utils\Manipulations;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ImageController
{
    public function __invoke(Request $request, string $fullpath, string $manipulations, string $filename): BinaryFileResponse
    {
        /** @var string $signKey */
        $signKey = Config::get('app.key');

        $manipArray = Manipulations::parse($manipulations);

        SignatureFactory::create($signKey)->validateRequest(
            $request->path(),
            array_merge($manipArray, ['s' => $request->query('s')])
        );
        /** @var string $publicFolder */
        $publicFolder = Config::get('opixlig.public_folder');
        /** @var string $storageFolder */
        $storageFolder = Config::get('opixlig.storage_folder');

        [$container, $path] = explode('/', ltrim($fullpath, '/'), 2);
        $inputPath = Storage::disk($container)->path($path);
        $cacheFolder = dirname($fullpath);
        $cacheDir = storage_path("$storageFolder/$cacheFolder");
        $originalFilename = pathinfo($inputPath, PATHINFO_BASENAME);
        $outputFolder = public_path("$publicFolder/$fullpath/$manipulations");

        $glideServer = ServerFactory::create([
            'source' => dirname($inputPath),
            'cache' => $cacheDir,
            'driver' => Config::get('opixlig.driver'),
        ]);

        File::ensureDirectoryExists($outputFolder);

        $conversionResult = $cacheDir.'/'.$glideServer->makeImage($originalFilename, $manipArray);
        rename($conversionResult, "$outputFolder/$filename");

        return response()->file("$outputFolder/$filename");
    }
}
