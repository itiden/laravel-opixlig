<?php

return [
    'storage_folder' => 'app/opixlig',
    'public_folder' => 'images',

    'driver' => 'imagick',

    'defaults' => [
        'widths' => [384, 640, 828, 1200, 1920, 2048, 3840],
        'placeholder' => 'empty',
        'quality' => 75,
        'format' => 'webp',
    ],

    'presets' => [
        // Example:
        // 'thumbnail' => [
        //     'w' => 150,
        //     'h' => 150,
        //     'q' => 60,
        //     'fit' => 'crop-center',
        //     'placeholder' => 'blur',
        // ],
    ],
];
