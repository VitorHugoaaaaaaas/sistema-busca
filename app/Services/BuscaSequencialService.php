<?php

namespace App\Services;

use App\Models\Registro;

class BuscaSequencialService
{
    /**
     * Retorna informações sobre o algoritmo
     */
    public static function getInfo()
    {
        return [
            'nome' => 'Busca Sequencial',
            'descricao' => 'Percorre os dados um a um e para imediatamente ao encontrar o primeiro registro correspondente.',
            'complexidade_media' => 'O(n)',
            'complexidade_pior' => 'O(n)',
            'uso_ideal' => 'Quando se deseja apenas encontrar a primeira ocorrência de um dado.'
        ];
    }

    /**
     * Método genérico de busca sequencial
     * Lógica: Percorre a lista e dá um BREAK assim que acha o primeiro
     */
    private function buscarGenerico($campo, $termo)
    {
        $inicio = microtime(true);
        
        // Carrega os dados para percorrer
        // Nota: Em um cenário real, "Registro::all()" carrega tudo na memória. 
        // Para a busca sequencial didática, isso é necessário para simular o loop PHP.
        $registros = Registro::all();
        
        $resultados = [];
        $comparacoes = 0;

        foreach ($registros as $registro) {
            $comparacoes++;
            
            // Verifica se o campo do registro é igual ao termo buscado
            // strcasecmp compara ignorando maiúsculas/minúsculas
            if (strcasecmp($registro->$campo, $termo) === 0) {
                
                // ACHOU! Adiciona este registro aos resultados
                $resultados[] = $registro;
                
                // --- O SEGREDO ESTÁ AQUI ---
                // O comando 'break' encerra o loop 'foreach' imediatamente.
                // Ele não vai olhar o próximo registro. Vai parar no primeiro ID que achou.
                break; 
            }
        }

        $tempoExecucao = (microtime(true) - $inicio) * 1000; // Tempo em ms

        return [
            'tempo_execucao' => $tempoExecucao,
            'total_encontrado' => count($resultados), // Será sempre 1 ou 0
            'resultados' => $resultados,
            'complexidade' => 'O(n)',
            'comparacoes_realizadas' => $comparacoes,
            'descricao' => count($resultados) > 0 
                ? "Busca Sequencial parou no primeiro registro encontrado após " . number_format($comparacoes) . " comparações."
                : "Busca Sequencial percorreu todos os " . number_format($comparacoes) . " registros e não encontrou nada."
        ];
    }

    // Métodos específicos chamados pelo Controller
    // Todos usam o buscarGenerico que tem o 'break'

    public function buscarPorNome($termo)
    {
        return $this->buscarGenerico('nome', $termo);
    }

    public function buscarPorCpf($termo)
    {
        return $this->buscarGenerico('cpf', $termo);
    }

    public function buscarPorCidade($termo)
    {
        return $this->buscarGenerico('cidade', $termo);
    }

    public function buscarPorEmail($termo)
    {
        return $this->buscarGenerico('email', $termo);
    }
}
