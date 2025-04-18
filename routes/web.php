<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdfviewer', function () {
    return view('pdfviewer');
});

Route::get('/pdf/viewer', function () {
    return view('pdf-viewer.index', [
        'pdf' => request('pdf'),
    ]);
})->name('pdf.viewer');

Route::get('/pdf', function () {
    return view('pdf');
})->name('pdf');
