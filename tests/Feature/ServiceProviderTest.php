<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Itiden\Opixlig\Http\Controllers\ImageController;

it('has merged opixlig configuration values available', function (): void {
    expect(Config::get('opixlig.storage_folder'))->toBe('app/opixlig')
        ->and(Config::get('opixlig.public_folder'))->toBe('images')
        ->and(Config::get('opixlig.default_quality'))->toBe(75)
        ->and(Config::get('opixlig.default_format'))->toBe('webp');
});

it('registers the opixlig view namespace', function (): void {
    expect(View::exists('opixlig::components.image'))->toBeTrue();

    $hints = $this->app['view']->getFinder()->getHints();

    expect($hints)->toHaveKey('opixlig');
});

it('registers the image route', function (): void {
    $route = collect($this->app['router']->getRoutes()->getRoutes())->first(fn ($candidate): bool => $candidate->uri() === 'images/{fullpath}/{manipulations}/{filename}'
        && in_array('GET', $candidate->methods(), true)
        && $candidate->getActionName() === ImageController::class);

    expect($route)->not->toBeNull();
});

it('merges filesystem links configuration for public image cache', function (): void {
    $links = Config::get('filesystems.links', []);
    $publicPath = $this->app->publicPath('images');

    expect($links)->toHaveKey($publicPath)
        ->and($links[$publicPath])->toBe($this->app->storagePath('app/opixlig'));
});
