<?php

use Illuminate\Support\Facades\Route;




Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/test', function () {
    return ['message' => 'API connectée 🎉'];
});
