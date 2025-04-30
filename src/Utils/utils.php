<?php

if (! function_exists('img')) {
    function img(string $src, int $width, int $height, array $baseManipulations = [])
    {
        return app(\Itiden\Opixlig\Services\ImageService::class, [
            'src' => $src,
            'width' => $width,
            'height' => $height,
            'baseManipulations' => $baseManipulations,
        ]);
    }
}
