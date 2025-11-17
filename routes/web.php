<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BuscaController;

// Página inicial
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de busca
Route::post('/buscar', [BuscaController::class, 'buscar'])->name('buscar');
Route::post('/comparar', [BuscaController::class, 'comparar'])->name('comparar');
