<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuscaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Rotas Principais
|--------------------------------------------------------------------------
|
| Rotas das páginas e funcionalidades principais do sistema
|
*/

// Página inicial
Route::get('/', [BuscaController::class, 'index'])->name('home');

// Página de pesquisa
Route::get('/pesquisar', [BuscaController::class, 'pesquisar'])->name('pesquisar');

// Executa a busca (POST)
Route::post('/buscar', [BuscaController::class, 'buscar'])->name('buscar');

// ⚡ NOVA ROTA - Comparar os 3 métodos de busca
Route::post('/comparar', [BuscaController::class, 'comparar'])->name('comparar');

// Página sobre
Route::get('/sobre', [BuscaController::class, 'sobre'])->name('sobre');

// Limpar cache
Route::post('/limpar-cache', [BuscaController::class, 'limparCache'])->name('limpar-cache');

/*
|--------------------------------------------------------------------------
| Rotas API (JSON)
|--------------------------------------------------------------------------
|
| Rotas que retornam dados em JSON
|
*/

// Estatísticas do sistema
Route::get('/api/estatisticas', [BuscaController::class, 'estatisticas'])->name('api.estatisticas');

// Informações sobre os tipos de busca
Route::get('/api/info-buscas', [BuscaController::class, 'infoBuscas'])->name('api.info-buscas');

/*
|--------------------------------------------------------------------------
| Rota Fallback
|--------------------------------------------------------------------------
|
| Captura rotas não encontradas e redireciona
|
*/

Route::fallback(function () {
    return redirect()->route('home')->with('error', 'Página não encontrada!');
});
