<?php

namespace Itiden\Opixlig;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Itiden\Opixlig\Listeners\StatamicAssetSubscriber;

final class OpixligServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/opixlig.php',
            'opixlig'
        );
        $this->publishes([
            __DIR__.'/../config/opixlig.php' => config_path('opixlig.php'),
        ], 'itiden-opixlig-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'opixlig');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->mergeFilesystemLinks();
    }

    public function register(): void
    {
        Event::subscribe(StatamicAssetSubscriber::class);
    }

    private function mergeFilesystemLinks(): void
    {
        /** @var array<string, string> $links */
        $links = Config::get('filesystems.links', []);
        /** @var string $cacheDir */
        $cacheDir = Config::get('opixlig.storage_folder');

        /** @var string $publicFolder */
        $publicFolder = Config::get('opixlig.public_folder');
        $links[public_path($publicFolder)] = storage_path($cacheDir);

        Config::set('filesystems.links', $links);
    }
}
