<?php

namespace Itiden\Opixlig\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

final class DeleteCachedImage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $container,
        public string $path,
    ) {}

    public function handle(): void
    {
        $storageFolder = Config::get('image.storage_folder');
        $cacheDir = storage_path("$storageFolder/{$this->container}/{$this->path}");

        if (File::isDirectory($cacheDir)) {
            File::deleteDirectory($cacheDir);
        }
    }
}
