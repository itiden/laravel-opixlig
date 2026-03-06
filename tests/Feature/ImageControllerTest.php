<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

it('serves a processed image with valid signature', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required.');
    }

    $resource = imagecreatetruecolor(10, 10);
    $red = imagecolorallocate($resource, 255, 0, 0);
    imagefill($resource, 0, 0, $red);

    ob_start();
    imagejpeg($resource);
    $image = (string) ob_get_clean();
    imagedestroy($resource);

    Storage::disk('public')->put('images/photo.jpg', $image);

    $service = img('public/images/photo.jpg', 100, 100, ['fm' => 'jpg', 'q' => 75]);
    $url = $service->url(['w' => 100]);

    $response = $this->get(parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY));

    $response->assertOk();
    $response->assertHeader('content-type', 'image/jpeg');
});

it('rejects requests with invalid signature', function (): void {
    $publicFolder = Config::get('opixlig.public_folder');

    $this->withoutExceptionHandling();

    $this->get("/{$publicFolder}/public/images/photo.jpg/fm-webp-q-75-w-800/photo.webp?s=invalidsignature");
})->throws(\League\Glide\Signatures\SignatureException::class);

it('serves an image with multiple manipulations in the URL', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required.');
    }

    $resource = imagecreatetruecolor(10, 10);
    $blue = imagecolorallocate($resource, 0, 0, 255);
    imagefill($resource, 0, 0, $blue);

    ob_start();
    imagejpeg($resource);
    $image = (string) ob_get_clean();
    imagedestroy($resource);

    Storage::disk('public')->put('images/multi.jpg', $image);

    $service = img('public/images/multi.jpg', 200, 200, ['fm' => 'jpg', 'q' => 90, 'fit' => 'crop']);
    $url = $service->url(['w' => 200]);

    $response = $this->get(parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY));

    $response->assertOk();
});
