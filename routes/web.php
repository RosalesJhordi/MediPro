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


Route::get('/plano-imprimir', function () {
    $datos = session('datos_lote', []);
    // dd($datos);
    return view('planos2d', compact('datos'));
})->name('plano.imprimir');


Route::get('/puertas-imprimir', function () {
    $datos = session('puertas', []);
    // dd($datos);
    return view('planos2dPuerta', compact('datos'));
})->name('puertas.imprimir');


//RUTA A AOPTIMIZADOR
Route::get('/optimizador', function () {
    return view('Opti');
})->name('optimizador');
