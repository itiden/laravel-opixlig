<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Itiden\Opixlig\Http\Controllers\ImageController;

$publicFolder = Config::get('opixlig.public_folder');

Route::get("/$publicFolder/{fullpath}/{manipulations}/{filename}", ImageController::class)
    ->where('fullpath', '.*');
