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
     * Executa a busca
     * 
     * Processa o formulário e executa os tipos de busca selecionados
     * Rota: POST /buscar
     */
    public function buscar(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'tipo_busca' => 'required|array|min:1',
            'tipo_busca.*' => 'in:sequencial,indexada,hashmap',
            'campo_busca' => 'required|in:nome,cpf,cidade,email',
            'termo_busca' => 'required|string|min:2',
        ], [
            'tipo_busca.required' => 'Selecione pelo menos um tipo de busca',
            'tipo_busca.*.in' => 'Tipo de busca inválido',
            'campo_busca.required' => 'Selecione o campo de busca',
            'campo_busca.in' => 'Campo de busca inválido',
            'termo_busca.required' => 'Digite o termo a ser buscado',
            'termo_busca.min' => 'O termo deve ter no mínimo 2 caracteres',
        ]);

        $resultados = [];
        $tiposBusca = $validated['tipo_busca'];
        $campo = $validated['campo_busca'];
        $termo = $validated['termo_busca'];

        // Executa cada tipo de busca selecionado
        foreach ($tiposBusca as $tipo) {
            switch ($tipo) {
                case 'sequencial':
                    $resultados['sequencial'] = $this->executarBuscaSequencial($campo, $termo);
                    break;
                    
                case 'indexada':
                    $resultados['indexada'] = $this->executarBuscaIndexada($campo, $termo);
                    break;
                    
                case 'hashmap':
                    $resultados['hashmap'] = $this->executarBuscaHashMap($campo, $termo);
                    break;
            }
        }

        // Calcula a diferença de performance
        $comparacao = $this->compararPerformance($resultados);

        return view('busca.resultados', compact(
            'resultados',
            'comparacao',
            'campo',
            'termo',
            'tiposBusca'
        ));
    }

    /**
     * ✅ NOVO MÉTODO ADICIONADO: Autocomplete
     * 
     * Retorna sugestões enquanto o usuário digita
     * Rota: POST /api/autocomplete
     */
    public function autocomplete(Request $request)
    {
        // Pega o termo da busca
        $termo = $request->input('termo', '');
        
        // Se o termo for muito curto, retorna vazio
        if (strlen($termo) < 2) {
            return response()->json([
                'success' => true,
                'resultados' => [],
                'total' => 0
            ]);
        }
        
        // Busca rápida no banco (máximo 10 resultados)
        $resultados = Registro::where(function($query) use ($termo) {
            $query->where('nome', 'LIKE', '%' . $termo . '%')
                  ->orWhere('email', 'LIKE', '%' . $termo . '%')
                  ->orWhere('cpf', 'LIKE', '%' . $termo . '%')
                  ->orWhere('cidade', 'LIKE', '%' . $termo . '%');
        })
        ->select('id', 'nome', 'email', 'cpf', 'cidade', 'estado', 'status')
        ->limit(10) // Limita a 10 sugestões
        ->get();
        
        return response()->json([
            'success' => true,
            'resultados' => $resultados,
            'total' => $resultados->count()
        ]);
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
