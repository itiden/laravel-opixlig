<?php

namespace Tests;

use Itiden\Opixlig\OpixligServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            OpixligServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:dGVzdGtleXRlc3RrZXl0ZXN0a2V5dGVzdGtleTE=');
        $app['config']->set('opixlig.storage_folder', 'app/opixlig');
        $app['config']->set('opixlig.public_folder', 'images');
        $app['config']->set('opixlig.driver', 'gd');
        $app['config']->set('opixlig.default_widths', [384, 640, 828, 1200, 1920, 2048, 3840]);
        $app['config']->set('opixlig.default_placeholder', 'empty');
        $app['config']->set('opixlig.default_quality', 75);
        $app['config']->set('opixlig.default_format', 'webp');
    }
}
