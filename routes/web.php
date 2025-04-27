<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pdf');
});

Route::get('/pdf-viewer', function () {
    return view('pdf-viewer', [
        'pdf' => request('pdf'),
    ]);
})->name('pdf-viewer');
