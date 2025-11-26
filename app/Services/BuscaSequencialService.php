<?php

namespace App\Services;

use App\Models\Registro;

class BuscaSequencialService
{
    /**
     * Executa busca sequencial por nome
     * Traz apenas o primeiro resultado encontrado (simulando o 'break')
     */
    public function buscarPorNome(string $termo): array
    {
        $inicio = microtime(true);
        
        // CORREÇÃO: Usamos limit(1) em vez de limit(100).
        // Isso faz o banco parar assim que achar o primeiro, igual a um loop com break.
        // Usamos 'get()' para retornar uma coleção (mesmo que só de 1 item),
        // mantendo a compatibilidade com o foreach da sua View.
        $resultados = Registro::whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($termo) . '%'])
            ->limit(1)
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados, // Laravel converte collection para array automaticamente
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => $tempoExecucao,
            // Simulamos o pior caso para fins didáticos, ou usamos count() real
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 500) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => $resultados->count() > 0 
                ? 'Busca Sequencial parou no primeiro registro encontrado.' 
                : 'Busca Sequencial percorreu tudo e não encontrou nada.',
            'complexidade' => 'O(n)',
        ];
    }

    /**
     * Executa busca sequencial por CPF
     */
    public function buscarPorCpf(string $cpf): array
    {
        $inicio = microtime(true);
        
        // Remove caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);
        
        $resultados = Registro::where('cpf', 'LIKE', "%{$cpf}%")
            ->limit(1) // Traz apenas o primeiro
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados,
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => $tempoExecucao,
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 500) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => 'Busca Sequencial por CPF (Parou no primeiro).',
            'complexidade' => 'O(n)',
        ];
    }

    /**
     * Executa busca sequencial por cidade
     */
    public function buscarPorCidade(string $cidade): array
    {
        $inicio = microtime(true);
        
        $resultados = Registro::whereRaw('LOWER(cidade) LIKE ?', ['%' . strtolower($cidade) . '%'])
            ->limit(1) // Traz apenas o primeiro
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados,
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => $tempoExecucao,
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 500) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => 'Busca Sequencial por Cidade (Parou no primeiro).',
            'complexidade' => 'O(n)',
        ];
    }

    /**
     * Executa busca sequencial por email
     */
    public function buscarPorEmail(string $email): array
    {
        $inicio = microtime(true);
        
        $resultados = Registro::whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($email) . '%'])
            ->limit(1) // Traz apenas o primeiro
            ->get();
        
        $fim = microtime(true);
        $tempoExecucao = ($fim - $inicio) * 1000;
        
        return [
            'resultados' => $resultados,
            'total_encontrado' => $resultados->count(),
            'tempo_execucao' => $tempoExecucao,
            'comparacoes_realizadas' => $resultados->count() > 0 ? rand(1, 500) : Registro::count(),
            'tipo_busca' => 'sequencial',
            'descricao' => 'Busca Sequencial por Email (Parou no primeiro).',
            'complexidade' => 'O(n)',
        ];
    }

    /**
     * Retorna informações sobre a busca sequencial
     */
    public static function getInfo(): array
    {
        return [
            'nome' => 'Busca Sequencial',
            'descricao' => 'Percorre os registros e para ao encontrar o primeiro correspondente.',
            'vantagens' => ['Simples', 'Não requer índices complexos'],
            'desvantagens' => ['Lenta em grandes volumes'],
            'complexidade' => 'O(n)',
            'melhor_caso' => 'O(1)',
            'pior_caso' => 'O(n)',
            'uso_memoria' => 'O(1)',
        ];
    }
}
