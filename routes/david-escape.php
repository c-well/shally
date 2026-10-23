<?php

use Illuminate\Support\Facades\Route;

Route::get('/kids/david-escape', fn () => response()->view('kids.david-escape')->header('Cache-Control', 'no-store, private'))->name('kids.david-escape');
