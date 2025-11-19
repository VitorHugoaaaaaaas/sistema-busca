<?php

/**
 * Arquivo de Rotas Web
 * 
 * Define todas as rotas HTTP do sistema.
 * Cada rota mapeia uma URL para um método do controller.
 * 
 * Localização: routes/web.php
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuscaController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
|
| Rotas acessíveis sem autenticação
|
*/

// Página inicial / Dashboard
Route::get('/', [BuscaController::class, 'index'])->name('home');

// Página de pesquisa
Route::get('/pesquisar', [BuscaController::class, 'pesquisar'])->name('pesquisar');

// Executa a busca (POST)
Route::post('/buscar', [BuscaController::class, 'buscar'])->name('buscar');

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
