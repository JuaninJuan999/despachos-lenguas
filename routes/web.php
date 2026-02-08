<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DespachoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de Despachos
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/despachos', [DespachoController::class, 'index'])->name('despachos.index');
    Route::get('/despachos/importar', [DespachoController::class, 'import'])->name('despachos.import');
    Route::post('/despachos', [DespachoController::class, 'store'])->name('despachos.store');
    Route::get('/despachos/{despacho}', [DespachoController::class, 'show'])->name('despachos.show');
    Route::get('/despachos/{despacho}/pdf', [DespachoController::class, 'generatePDF'])->name('despachos.pdf');
    Route::get('/despachos/{despacho}/llaves', [DespachoController::class, 'generateImagenLlaves'])->name('despachos.llaves');
});

require __DIR__.'/auth.php';
