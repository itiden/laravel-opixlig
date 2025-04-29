<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

$publicFolder = Config::get('image.public_folder');

Route::get("/$publicFolder/{fullpath}/{manipulations}/{filename}", \Itiden\LaravelImage\Http\Controllers\ImageController::class)
    ->where('fullpath', '.*');
