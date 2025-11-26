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
     * Lógica: Usa cursor() para economizar memória e break para parar no primeiro.
     */
    private function buscarGenerico($campo, $termo)
    {
        $inicio = microtime(true);
        
        // CORREÇÃO AQUI: Usamos cursor() em vez de all()
        // O cursor carrega os registros sob demanda, evitando erro de memória no Railway
        $registros = Registro::cursor();
        
        $resultados = [];
        $comparacoes = 0;

        foreach ($registros as $registro) {
            $comparacoes++;
            
            // Verifica se o campo do registro é igual ao termo buscado
            // strcasecmp compara ignorando maiúsculas/minúsculas
            if (strcasecmp($registro->$campo, $termo) === 0) {
                
                // ACHOU! Adiciona este registro aos resultados
                $resultados[] = $registro;
                
                // O SEGREDO ESTÁ AQUI: PARA IMEDIATAMENTE
                break; 
            }
        }

        $tempoExecucao = (microtime(true) - $inicio) * 1000; // Tempo em ms

        return [
            'tempo_execucao' => $tempoExecucao,
            'total_encontrado' => count($resultados),
            'resultados' => $resultados, // Retornará apenas 1 ou 0
            'complexidade' => 'O(n)',
            'comparacoes_realizadas' => $comparacoes,
            'descricao' => count($resultados) > 0 
                ? "Busca Sequencial parou no primeiro registro encontrado após " . number_format($comparacoes) . " comparações."
                : "Busca Sequencial percorreu todos os " . number_format($comparacoes) . " registros e não encontrou nada."
        ];
    }

    // Métodos específicos chamados pelo Controller

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
