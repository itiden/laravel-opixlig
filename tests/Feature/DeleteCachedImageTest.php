<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Itiden\Opixlig\Jobs\DeleteCachedImage;

it('can be instantiated with container and path', function (): void {
    $job = new DeleteCachedImage('public', 'images/photo.jpg');

    expect($job->container)->toBe('public')
        ->and($job->path)->toBe('images/photo.jpg');
});

it('deletes the cache directory when it exists', function (): void {
    $storageFolder = Config::get('opixlig.storage_folder');
    $cacheDir = storage_path("{$storageFolder}/public/images");

    File::ensureDirectoryExists($cacheDir);
    File::put("{$cacheDir}/cached.jpg", 'fake image');

    expect(File::isDirectory($cacheDir))->toBeTrue();

    $job = new DeleteCachedImage('public', 'images');
    $job->handle();

    expect(File::isDirectory($cacheDir))->toBeFalse();
});

it('does nothing when the cache directory does not exist', function (): void {
    $storageFolder = Config::get('opixlig.storage_folder');
    $cacheDir = storage_path("{$storageFolder}/public/nonexistent");

    expect(File::isDirectory($cacheDir))->toBeFalse();

    $job = new DeleteCachedImage('public', 'nonexistent');
    $job->handle();

    expect(File::isDirectory($cacheDir))->toBeFalse();
});
