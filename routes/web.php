<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DespachoController;

Route::get('/', function () {
    return view('welcome');
});

// 🆕 Rutas para Despacho de Lenguas
Route::get('/importar', [DespachoController::class, 'showImport'])->name('importar.form');
Route::post('/importar', [DespachoController::class, 'importExcel'])->name('importar.excel');
