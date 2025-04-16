<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdfviewer', function () {
    return view('pdfviewer');
});
