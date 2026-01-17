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


Route::get('/imprimir-plano', function () {
    $data = session('datos_plano');
    if (!$data) return redirect('/Ventana'); // Por si no hay datos

    return view('planos2d', $data);
});
Route::get('/imprimir-plano', function () {
    // Recuperamos los datos de la sesión (los guardaremos al hacer clic)
    $data = session('datos_plano');
    if (!$data) return "No hay datos para mostrar";

    return view('planos2d', $data);
})->name('plano.imprimir');
