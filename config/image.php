<?php

return [
    'storage_folder' => 'app/.images',
    'public_folder' => 'images',

    'driver' => 'imagick',

    'default_widths' => [/* 256, */ 384, 640 /* , 750 */, 828 /* , 1080 */, 1200 /* , 1920 */, 2048, 3840],

    'default_placeholder' => 'empty',
    'default_quality' => 75,
];
