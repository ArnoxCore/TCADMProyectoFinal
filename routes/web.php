<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\CitaController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('landing');
});

// Dashboard (protegido)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Panel del cliente (de tu compa)
Route::get('/panel-cliente', [CitaController::class, 'panelCliente'])
    ->middleware('auth')
    ->name('panel.cliente');

// Crear cita (de tu compa)
Route::post('/citas', [CitaController::class, 'store'])
    ->middleware('auth')
    ->name('citas.store');

// Perfil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Logout rápido de pruebas
Route::get('/dev-logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('dev.logout');

// Rutas Breeze (login / register)
require __DIR__.'/auth.php';
