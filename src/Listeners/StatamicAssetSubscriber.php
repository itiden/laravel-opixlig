<?php

namespace Itiden\Opixlig\Listeners;

use Illuminate\Events\Dispatcher;
use Itiden\Opixlig\Jobs\DeleteCachedImage;

final class StatamicAssetSubscriber
{
    public function handle(object $event): void
    {
        /** @var object&\stdClass $event */
        $asset = $event->asset;
        $path = $asset->path;
        $isImage = $asset->is_image;
        $container = $asset->container->handle;

        if (! $isImage) {
            return;
        }

        dispatch(new DeleteCachedImage($container, $path));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            'Statamic\Events\AssetDeleted',
            (new StatamicAssetSubscriber)->handle(...)
        );

        $events->listen(
            'Statamic\Events\AssetSaved',
            (new StatamicAssetSubscriber)->handle(...)
        );

        // Statamic\Events\AssetFolderDeleted
        // Remove the folder from the cache?
    }
}
