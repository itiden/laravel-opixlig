<?php

namespace Itiden\Opixlig;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Itiden\Opixlig\Listeners\StatamicAssetSubscriber;

final class ImageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/image.php',
            'image'
        );
        $this->publishes([
            __DIR__.'/../config/image.php' => config_path('image.php'),
        ], 'itiden-opixlig-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'opixlig');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->mergeFilesystemLinks();
    }

    public function register()
    {
        Event::subscribe(StatamicAssetSubscriber::class);
    }

    protected function mergeFilesystemLinks(): void
    {
        $links = Config::get('filesystems.links', []);
        $cacheDir = Config::get('image.cache_dir');

        $links[public_path(Config::get('image.public_folder'))] = storage_path($cacheDir);

        Config::set('filesystems.links', $links);
    }
}
