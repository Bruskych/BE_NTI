<?php

use Illuminate\Support\Facades\Route;

// Веб-маршрут по умолчанию — отображает стартовую страницу Laravel
Route::get('/', function () {
    return view('welcome');
});
