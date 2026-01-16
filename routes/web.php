<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('Ventana', function () {
    return view('Ventana');
});

Route::get('Puerta', function (){
    return view('Puerta');
});
