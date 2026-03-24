<?php

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Bus;
use Itiden\Opixlig\Jobs\DeleteCachedImage;
use Itiden\Opixlig\Listeners\StatamicAssetSubscriber;

it('dispatches DeleteCachedImage job for image assets', function (): void {
    Bus::fake();

    $event = new stdClass;
    $event->asset = new stdClass;
    $event->asset->path = 'photos/hero.jpg';
    $event->asset->is_image = true;
    $event->asset->container = new stdClass;
    $event->asset->container->handle = 'assets';

    $subscriber = new StatamicAssetSubscriber;
    $subscriber->handle($event);

    Bus::assertDispatched(DeleteCachedImage::class, fn ($job): bool => $job->container === 'assets' && $job->path === 'photos/hero.jpg');
});

it('skips non-image assets', function (): void {
    Bus::fake();

    $event = new stdClass;
    $event->asset = new stdClass;
    $event->asset->path = 'documents/readme.pdf';
    $event->asset->is_image = false;
    $event->asset->container = new stdClass;
    $event->asset->container->handle = 'assets';

    $subscriber = new StatamicAssetSubscriber;
    $subscriber->handle($event);

    Bus::assertNotDispatched(DeleteCachedImage::class);
});

it('subscribes to Statamic asset events', function (): void {
    $subscriber = new StatamicAssetSubscriber;
    $dispatcher = app(Dispatcher::class);

    $subscriber->subscribe($dispatcher);

    expect($dispatcher->hasListeners('Statamic\Events\AssetDeleted'))->toBeTrue()
        ->and($dispatcher->hasListeners('Statamic\Events\AssetSaved'))->toBeTrue();
});
