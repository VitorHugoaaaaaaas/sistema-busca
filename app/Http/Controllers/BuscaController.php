<?php

/**
 * Controller: BuscaController
 * 
 * Controlador principal do sistema de buscas.
 * Gerencia as requisições HTTP e coordena os diferentes tipos de busca.
 * 
 * Localização: app/Http/Controllers/BuscaController.php
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuscaSequencialService;
use App\Services\BuscaIndexadaService;
use App\Services\BuscaHashMapService;
use App\Models\Registro;

class BuscaController extends Controller
{
    /**
     * Instâncias dos serviços de busca
     */
    private $buscaSequencial;
    private $buscaIndexada;
    private $buscaHashMap;

    /**
     * Construtor - injeta os serviços
     */
    public function __construct(
        BuscaSequencialService $buscaSequencial,
        BuscaIndexadaService $buscaIndexada,
        BuscaHashMapService $buscaHashMap
    ) {
        $this->buscaSequencial = $buscaSequencial;
        $this->buscaIndexada = $buscaIndexada;
        $this->buscaHashMap = $buscaHashMap;
    }

    /**
     * Página inicial - Dashboard
     * 
     * Exibe estatísticas e informações sobre o sistema
     * Rota: GET /
     */
    public function index()
    {
        // Busca estatísticas do banco
        $stats = Registro::estatisticas();
        
        // Informações sobre cada tipo de busca
        $infoSequencial = BuscaSequencialService::getInfo();
        $infoIndexada = BuscaIndexadaService::getInfo();
        $infoHashMap = BuscaHashMapService::getInfo();
        
        return view('busca.index', compact(
            'stats',
            'infoSequencial',
            'infoIndexada',
            'infoHashMap'
        ));
    }

    /**
     * Página de pesquisa
     * 
     * Exibe o formulário de busca
     * Rota: GET /pesquisar
     */
    public function pesquisar()
    {
        return view('busca.pesquisar');
    }

    /**
     * MÉTODO ATUALIZADO: Executa a busca (compatível com a nova view)
     * 
     * Processa requisições AJAX do formulário
     * Rota: POST /buscar
     */
    public function buscar(Request $request)
    {
        // Validação dos dados para o novo formato
        $validated = $request->validate([
            'termo' => 'required|string|min:1',
            'metodo' => 'required|in:sequencial,indexada,hashmap'
        ]);

        $termo = $validated['termo'];
        $metodo = $validated['metodo'];
        
        // Registra o tempo de início
        $startTime = microtime(true);
        
        // Executa a busca de acordo com o método selecionado
        $resultados = [];
        
        switch ($metodo) {
            case 'sequencial':
                $resultados = $this->executarBuscaCompleta($termo, 'sequencial');
                break;
                
            case 'indexada':
                $resultados = $this->executarBuscaCompleta($termo, 'indexada');
                break;
                
            case 'hashmap':
                $resultados = $this->executarBuscaCompleta($termo, 'hashmap');
                break;
        }
        
        // Calcula o tempo de execução
        $endTime = microtime(true);
        $tempoExecucao = round(($endTime - $startTime) * 1000, 2); // em milissegundos
        
        // Retorna resposta JSON para requisições AJAX
        return response()->json([
            'success' => true,
            'resultados' => $resultados,
            'total' => count($resultados),
            'tempo' => $tempoExecucao,
            'metodo' => $metodo
        ]);
    }

    /**
     * NOVO MÉTODO: Compara todos os métodos de busca
     * 
     * Executa os três tipos de busca e retorna análise comparativa
     * Rota: POST /comparar
     */
    public function comparar(Request $request)
    {
        $request->validate([
            'termo' => 'required|string|min:1'
        ]);

        $termo = $request->input('termo');
        
        // Array para armazenar os resultados de cada método
        $resultados = [];
        
        // 1. BUSCA SEQUENCIAL
        $startTime = microtime(true);
        $sequencialResults = $this->executarBuscaCompleta($termo, 'sequencial');
        $endTime = microtime(true);
        
        $resultados['sequencial'] = [
            'tempo' => round(($endTime - $startTime) * 1000, 2),
            'total' => count($sequencialResults),
            'metodo' => 'sequencial',
            'resultados' => $sequencialResults
        ];
        
        // 2. BUSCA INDEXADA
        $startTime = microtime(true);
        $indexadaResults = $this->executarBuscaCompleta($termo, 'indexada');
        $endTime = microtime(true);
        
        $resultados['indexada'] = [
            'tempo' => round(($endTime - $startTime) * 1000, 2),
            'total' => count($indexadaResults),
            'metodo' => 'indexada',
            'resultados' => $indexadaResults
        ];
        
        // 3. BUSCA HASHMAP
        $startTime = microtime(true);
        $hashmapResults = $this->executarBuscaCompleta($termo, 'hashmap');
        $endTime = microtime(true);
        
        $resultados['hashmap'] = [
            'tempo' => round(($endTime - $startTime) * 1000, 2),
            'total' => count($hashmapResults),
            'metodo' => 'hashmap',
            'resultados' => $hashmapResults
        ];
        
        // Análise dos resultados
        $analise = $this->analisarResultadosComparacao($resultados);
        
        return response()->json([
            'success' => true,
            'resultados' => $resultados,
            'analise' => $analise
        ]);
    }

    /**
     * NOVO MÉTODO: Executa busca completa em todos os campos
     * 
     * @param string $termo Termo de busca
     * @param string $metodo Método de busca a usar
     * @return array Resultados encontrados
     */
    private function executarBuscaCompleta(string $termo, string $metodo): array
    {
        $resultadosCombinados = [];
        $idsJaIncluidos = [];
        
        // Busca em todos os campos
        $campos = ['nome', 'cpf', 'cidade', 'email'];
        
        foreach ($campos as $campo) {
            $resultadosCampo = [];
            
            switch ($metodo) {
                case 'sequencial':
                    $resultadosCampo = $this->executarBuscaSequencial($campo, $termo);
                    break;
                    
                case 'indexada':
                    $resultadosCampo = $this->executarBuscaIndexada($campo, $termo);
                    break;
                    
                case 'hashmap':
                    $resultadosCampo = $this->executarBuscaHashMap($campo, $termo);
                    break;
            }
            
            // Adiciona apenas registros únicos
            if (isset($resultadosCampo['dados'])) {
                foreach ($resultadosCampo['dados'] as $registro) {
                    if (!in_array($registro->id, $idsJaIncluidos)) {
                        $resultadosCombinados[] = $registro;
                        $idsJaIncluidos[] = $registro->id;
                    }
                }
            }
        }
        
        return $resultadosCombinados;
    }

    /**
     * NOVO MÉTODO: Analisa resultados da comparação
     * 
     * @param array $resultados Resultados dos três métodos
     * @return array Análise comparativa
     */
    private function analisarResultadosComparacao($resultados)
    {
        // Encontrar o método mais rápido
        $maisRapida = null;
        $tempoMinimo = PHP_INT_MAX;
        
        foreach ($resultados as $metodo => $resultado) {
            if ($resultado['tempo'] < $tempoMinimo) {
                $tempoMinimo = $resultado['tempo'];
                $maisRapida = [
                    'metodo' => $metodo,
                    'tempo' => $resultado['tempo']
                ];
            }
        }
        
        // Calcular economia do HashMap em relação ao Sequencial
        $economiaHashmap = 0;
        if ($resultados['sequencial']['tempo'] > 0) {
            $economiaHashmap = (($resultados['sequencial']['tempo'] - $resultados['hashmap']['tempo']) 
                / $resultados['sequencial']['tempo']) * 100;
        }
        
        // Calcular economia da Indexada em relação ao Sequencial
        $economiaIndexada = 0;
        if ($resultados['sequencial']['tempo'] > 0) {
            $economiaIndexada = (($resultados['sequencial']['tempo'] - $resultados['indexada']['tempo']) 
                / $resultados['sequencial']['tempo']) * 100;
        }
        
        return [
            'mais_rapida' => $maisRapida,
            'economia_hashmap' => round($economiaHashmap, 2),
            'economia_indexada' => round($economiaIndexada, 2),
            'diferenca_tempo' => [
                'sequencial_vs_indexada' => round($resultados['sequencial']['tempo'] - $resultados['indexada']['tempo'], 2),
                'sequencial_vs_hashmap' => round($resultados['sequencial']['tempo'] - $resultados['hashmap']['tempo'], 2),
                'indexada_vs_hashmap' => round($resultados['indexada']['tempo'] - $resultados['hashmap']['tempo'], 2)
            ]
        ];
    }

    /**
     * Executa busca sequencial
     * 
     * @param string $campo Campo a ser buscado
     * @param string $termo Termo da busca
     * @return array Resultado da busca
     */
    private function executarBuscaSequencial(string $campo, string $termo): array
    {
        switch ($campo) {
            case 'nome':
                return $this->buscaSequencial->buscarPorNome($termo);
            case 'cpf':
                return $this->buscaSequencial->buscarPorCpf($termo);
            case 'cidade':
                return $this->buscaSequencial->buscarPorCidade($termo);
            case 'email':
                // Se não houver método específico, usa buscarPorNome como fallback
                return method_exists($this->buscaSequencial, 'buscarPorEmail') 
                    ? $this->buscaSequencial->buscarPorEmail($termo)
                    : $this->buscaSequencial->buscarPorNome($termo);
            default:
                return $this->buscaSequencial->buscarPorNome($termo);
        }
    }

    /**
     * Executa busca indexada
     * 
     * @param string $campo Campo a ser buscado
     * @param string $termo Termo da busca
     * @return array Resultado da busca
     */
    private function executarBuscaIndexada(string $campo, string $termo): array
    {
        switch ($campo) {
            case 'nome':
                return $this->buscaIndexada->buscarPorNome($termo);
            case 'cpf':
                return $this->buscaIndexada->buscarPorCpf($termo);
            case 'cidade':
                return $this->buscaIndexada->buscarPorCidade($termo);
            case 'email':
                return $this->buscaIndexada->buscarPorEmail($termo);
            default:
                return $this->buscaIndexada->buscarPorNome($termo);
        }
    }

    /**
     * Executa busca por HashMap
     * 
     * @param string $campo Campo a ser buscado
     * @param string $termo Termo da busca
     * @return array Resultado da busca
     */
    private function executarBuscaHashMap(string $campo, string $termo): array
    {
        switch ($campo) {
            case 'nome':
                return $this->buscaHashMap->buscarPorNome($termo);
            case 'cpf':
                return $this->buscaHashMap->buscarPorCpf($termo);
            case 'cidade':
                return $this->buscaHashMap->buscarPorCidade($termo);
            case 'email':
                // Se não houver método específico, usa buscarPorNome como fallback
                return method_exists($this->buscaHashMap, 'buscarPorEmail')
                    ? $this->buscaHashMap->buscarPorEmail($termo)
                    : $this->buscaHashMap->buscarPorNome($termo);
            default:
                return $this->buscaHashMap->buscarPorNome($termo);
        }
    }

    /**
     * Compara a performance entre os tipos de busca
     * 
     * @param array $resultados Resultados de todas as buscas
     * @return array Comparação de performance
     */
    private function compararPerformance(array $resultados): array
    {
        if (empty($resultados)) {
            return [];
        }

        $tempos = [];
        $maisRapido = null;
        $maisLento = null;

        // Coleta os tempos
        foreach ($resultados as $tipo => $resultado) {
            $tempo = $resultado['tempo_execucao'];
            $tempos[$tipo] = $tempo;

            if ($maisRapido === null || $tempo < $tempos[$maisRapido]) {
                $maisRapido = $tipo;
            }

            if ($maisLento === null || $tempo > $tempos[$maisLento]) {
                $maisLento = $tipo;
            }
        }

        // Calcula diferenças percentuais
        $diferencas = [];
        $tempoBase = $tempos[$maisRapido];

        foreach ($tempos as $tipo => $tempo) {
            if ($tipo === $maisRapido) {
                $diferencas[$tipo] = 0;
            } else {
                $diferenca = (($tempo - $tempoBase) / $tempoBase) * 100;
                $diferencas[$tipo] = round($diferenca, 2);
            }
        }

        return [
            'tempos' => $tempos,
            'mais_rapido' => $maisRapido,
            'mais_lento' => $maisLento,
            'diferencas' => $diferencas,
            'tempo_economizado' => $tempos[$maisLento] - $tempos[$maisRapido],
        ];
    }

    /**
     * Página "Sobre"
     * 
     * Informações sobre o projeto e as tecnologias
     * Rota: GET /sobre
     */
    public function sobre()
    {
        return view('busca.sobre');
    }

    /**
     * Limpa o cache dos HashMaps
     * 
     * Rota: POST /limpar-cache
     */
    public function limparCache()
    {
        $this->buscaHashMap->limparCache();
        
        return redirect()->back()->with('success', 'Cache dos HashMaps limpo com sucesso!');
    }

    /**
     * API: Retorna estatísticas do sistema
     * 
     * Rota: GET /api/estatisticas
     */
    public function estatisticas()
    {
        return response()->json(Registro::estatisticas());
    }

    /**
     * API: Retorna informações sobre os tipos de busca
     * 
     * Rota: GET /api/info-buscas
     */
    public function infoBuscas()
    {
        return response()->json([
            'sequencial' => BuscaSequencialService::getInfo(),
            'indexada' => BuscaIndexadaService::getInfo(),
            'hashmap' => BuscaHashMapService::getInfo(),
        ]);
    }
}
