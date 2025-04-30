<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

$publicFolder = Config::get('opixlig.public_folder');

Route::get("/$publicFolder/{fullpath}/{manipulations}/{filename}", \Itiden\Opixlig\Http\Controllers\ImageController::class)
    ->where('fullpath', '.*');
