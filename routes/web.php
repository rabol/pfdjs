<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('esign');
});

Route::get('/pdf-viewer', function () {
    return view('pdf-viewer', [
        'pdf' => request('pdf'),
    ]);
})->name('pdf-viewer');

Route::get('/page-viewer', function () {
    return view('page-viewer', [
        'pdf' => request('pdf'),
    ]);
})->name('page-viewer');

Route::get('/test', function () {
    return view('test');
});
