<?php

if (! function_exists('img')) { // @codeCoverageIgnore
    /** @param array<string, string|int> $baseManipulations */
    function img(string $src, int $width = 0, int $height = 0, array $baseManipulations = [], string $preset = ''): \Itiden\Opixlig\Services\ImageService
    {
        if ($preset !== '') {
            $resolved = \Itiden\Opixlig\Utils\Manipulations::resolve($preset, array_filter([
                'w' => $width,
                'h' => $height,
            ], static fn (int $v): bool => $v !== 0));

            $width = $resolved['w'];
            $height = $resolved['h'];
            $baseManipulations = array_merge($resolved['manipulations'], $baseManipulations);
        }

        return app(\Itiden\Opixlig\Services\ImageService::class, [
            'src' => $src,
            'width' => $width,
            'height' => $height,
            'baseManipulations' => $baseManipulations,
        ]);
    }
}
