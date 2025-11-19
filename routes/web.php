<?php

use Illuminate\Support\Facades\Route;

// Controladores del módulo Cliente
use App\Http\Controllers\Cliente\DashboardController;
use App\Http\Controllers\Cliente\CitaController;
use App\Http\Controllers\Cliente\VehiculoController;
use App\Http\Controllers\Cliente\PerfilController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirección inteligente según rol
Route::get('/redirigir', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $role = auth()->user()->role_id;

    switch ($role) {
        case 1:
            return redirect()->route('cliente.dashboard');
        case 2:
            return redirect()->route('mecanico.dashboard');
        case 3:
            return redirect()->route('recepcion.dashboard');
        case 4:
            return redirect()->route('admin.dashboard');
        default:
            return redirect()->route('login');
    }
})->name('redirigir');

// Landing Page pública
Route::get('/', function () {
    return view('landing');
})->name('landing');


// ======================================================
//  RUTAS DEL CLIENTE (ROL 1)
//  URL base: /mi-cuenta
//  Todas requieren autenticación y rol = cliente
// ======================================================
Route::middleware(['auth', 'role:1'])
    ->prefix('mi-cuenta')
    ->name('cliente.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // ----------------------- C I T A S -----------------------
        Route::prefix('citas')->name('citas.')->group(function () {

            Route::get('/', [CitaController::class, 'index'])->name('index');

            Route::get('/crear', [CitaController::class, 'create'])->name('crear');

            Route::post('/', [CitaController::class, 'store'])->name('store');

            Route::get('/{id}', [CitaController::class, 'show'])->name('show');

            Route::post('/{id}/cancelar', [CitaController::class, 'cancel'])->name('cancelar');
        });

        // --------------------- V E H I C U L O S ---------------------
        Route::prefix('vehiculos')->name('vehiculos.')->group(function () {

            Route::get('/', [VehiculoController::class, 'index'])->name('index');

            Route::get('/crear', [VehiculoController::class, 'create'])->name('crear');

            Route::post('/', [VehiculoController::class, 'store'])->name('store');

            Route::get('/{id}/editar', [VehiculoController::class, 'edit'])->name('editar');

            Route::put('/{id}', [VehiculoController::class, 'update'])->name('update');
        });

        // ------------------------ P E R F I L ------------------------
        Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil');

        Route::patch('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    });


// ======================================================
//  RUTAS DEL MECÁNICO (ROL 2)
//  URL base: /mecanico
// ======================================================
Route::middleware(['auth', 'role:2'])
    ->prefix('mecanico')
    ->name('mecanico.')
    ->group(function () {
        Route::get('/', function () {
            return 'Panel del mecánico (en construcción)';
        })->name('dashboard');
    });


// ======================================================
//  RUTAS DE LA RECEPCIONISTA (ROL 3)
//  URL base: /recepcion
// ======================================================
Route::middleware(['auth', 'role:3'])
    ->prefix('recepcion')
    ->name('recepcion.')
    ->group(function () {
        Route::get('/', function () {
            return 'Panel de recepción (en construcción)';
        })->name('dashboard');
    });


// ======================================================
//  RUTAS DEL ADMIN (ROL 4)
//  URL base: /admin
// ======================================================
Route::middleware(['auth', 'role:4'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return 'Panel del administrador (en construcción)';
        })->name('dashboard');
    });


// Rutas Breeze (login, register, password reset...)
require __DIR__.'/auth.php';
