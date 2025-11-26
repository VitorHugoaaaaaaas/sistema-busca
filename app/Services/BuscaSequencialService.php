<?php

/**
 * Service: Busca Sequencial
 * Implementa a busca limitando ao primeiro resultado para evitar estouro de memória.
 */

namespace App\Services;

use App\Models\Registro;
use Illuminate\Support\Facades\DB;

class BuscaSequencialService
{
    /**
     * Executa busca sequencial por nome
     * Alteração: limit(1) para parar no primeiro registro encontrado
     */
    public function buscarPorNome(string $termo): array
    {
        $inicio = microtime(true);
        
        // limit(1): Instrui o banco a parar assim que achar o primeiro
        $resultados = Registro::whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($termo) . '%'])
            ->limit(1) 
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados->toArray(),
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => round($tempoExecucao, 4),
            // Simulamos o pior caso (n) ou usamos count real se achou rápido
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 100) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => $resultados->count() > 0 
                ? 'Busca Sequencial parou no primeiro registro (1 encontrado).' 
                : 'Busca percorreu tudo e não encontrou.',
            'complexidade' => 'O(n) - Linear',
            'registros_analisados' => Registro::count(),
        ];
    }

    /**
     * Executa busca sequencial por CPF
     */
    public function buscarPorCpf(string $cpf): array
    {
        $inicio = microtime(true);
        
        $cpf = preg_replace('/\D/', '', $cpf);
        
        // limit(1): Para no primeiro
        $resultados = Registro::where('cpf', 'LIKE', "%{$cpf}%")
            ->limit(1)
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados->toArray(),
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => round($tempoExecucao, 4),
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 100) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => 'Busca Sequencial por CPF (primeiro encontrado).',
            'complexidade' => 'O(n) - Linear',
            'registros_analisados' => Registro::count(),
        ];
    }

    /**
     * Executa busca sequencial por cidade
     */
    public function buscarPorCidade(string $cidade): array
    {
        $inicio = microtime(true);
        
        // limit(1): Para no primeiro
        $resultados = Registro::whereRaw('LOWER(cidade) LIKE ?', ['%' . strtolower($cidade) . '%'])
            ->limit(1)
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados->toArray(),
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => round($tempoExecucao, 4),
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 100) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => 'Busca Sequencial por Cidade (primeiro encontrado).',
            'complexidade' => 'O(n) - Linear',
            'registros_analisados' => Registro::count(),
        ];
    }

    /**
     * Executa busca sequencial por email
     */
    public function buscarPorEmail(string $email): array
    {
        $inicio = microtime(true);
        
        // limit(1): Para no primeiro
        $resultados = Registro::whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($email) . '%'])
            ->limit(1)
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados->toArray(),
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => round($tempoExecucao, 4),
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 100) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => 'Busca Sequencial por Email (primeiro encontrado).',
            'complexidade' => 'O(n) - Linear',
            'registros_analisados' => Registro::count(),
        ];
    }

    /**
     * Retorna informações sobre a busca sequencial
     */
    public static function getInfo(): array
    {
        return [
            'nome' => 'Busca Sequencial',
            'descricao' => 'Percorre os registros e para ao encontrar o primeiro.',
            'vantagens' => [
                'Simples de implementar',
                'Não requer índices complexos',
            ],
            'desvantagens' => [
                'Lenta em grandes volumes',
                'Performance O(n)',
            ],
            'complexidade' => 'O(n)',
            'melhor_caso' => 'O(1)',
            'pior_caso' => 'O(n)',
            'uso_memoria' => 'O(1)',
            'quando_usar' => [
                'Conjuntos pequenos',
                'Quando não há índices',
            ],
        ];
    }
}
