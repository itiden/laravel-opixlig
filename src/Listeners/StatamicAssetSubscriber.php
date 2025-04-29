<?php

namespace Itiden\LaravelImage\Listeners;

use Illuminate\Events\Dispatcher;
use Itiden\LaravelImage\Jobs\DeleteCachedImage;

final class StatamicAssetSubscriber
{
    public function handle($event): void
    {
        $asset = $event->asset;
        $path = $asset->path;
        $isImage = $asset->is_image;
        $container = $asset->container->handle;

        if (! $isImage) {
            return;
        }

        dispatch(new DeleteCachedImage($container, $path));
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            'Statamic\Events\AssetDeleted',
            [StatamicAssetSubscriber::class, 'handle']
        );

        $events->listen(
            'Statamic\Events\AssetSaved',
            [StatamicAssetSubscriber::class, 'handle']
        );

        // Statamic\Events\AssetFolderDeleted
        // Remove the folder from the cache?
    }
}
