<?php
use App\Http\Controllers\CitaController;

Route::get('/panel-cliente', [CitaController::class, 'index'])->name('panel.cliente');

Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
