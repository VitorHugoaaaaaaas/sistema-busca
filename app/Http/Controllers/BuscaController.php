<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuscaSequencialService;
use App\Services\BuscaIndexadaService;
use App\Services\BuscaHashMapService;
use App\Models\Registro;
use Illuminate\Support\Facades\Log;

class BuscaController extends Controller
{
    private $buscaSequencial;
    private $buscaIndexada;
    private $buscaHashMap;

    public function __construct(
        BuscaSequencialService $buscaSequencial,
        BuscaIndexadaService $buscaIndexada,
        BuscaHashMapService $buscaHashMap
    ) {
        $this->buscaSequencial = $buscaSequencial;
        $this->buscaIndexada = $buscaIndexada;
        $this->buscaHashMap = $buscaHashMap;
    }

    public function index()
    {
        $stats = Registro::estatisticas();
        $infoSequencial = BuscaSequencialService::getInfo();
        $infoIndexada = BuscaIndexadaService::getInfo();
        $infoHashMap = BuscaHashMapService::getInfo();
        
        return view('busca.index', compact('stats', 'infoSequencial', 'infoIndexada', 'infoHashMap'));
    }

    public function pesquisar()
    {
        // Traz 10 registros aleatórios para preencher a tabela de exemplo
        $registros = Registro::inRandomOrder()->limit(10)->get();
        return view('busca.pesquisar', compact('registros'));
    }

    public function buscar(Request $request)
    {
        $validated = $request->validate([
            'tipo_busca' => 'required|array|min:1',
            'tipo_busca.*' => 'in:sequencial,indexada,hashmap',
            'campo_busca' => 'required|in:nome,cpf,cidade,email',
            'termo_busca' => 'required|string|min:2',
        ]);

        $resultados = [];
        $tiposBusca = $validated['tipo_busca'];
        $campo = $validated['campo_busca'];
        $termo = $validated['termo_busca'];

        foreach ($tiposBusca as $tipo) {
            // INICIO DA CORREÇÃO: TRY-CATCH PARA EVITAR ERRO 500
            try {
                $inicio = microtime(true);
                
                switch ($tipo) {
                    case 'sequencial':
                        $res = $this->executarBuscaSequencial($campo, $termo);
                        break;
                    case 'indexada':
                        $res = $this->executarBuscaIndexada($campo, $termo);
                        break;
                    case 'hashmap':
                        $res = $this->executarBuscaHashMap($campo, $termo);
                        break;
                }
                
                $resultados[$tipo] = $res;

            } catch (\Throwable $e) {
                // Se der erro (como o erro 500), capturamos aqui e não quebramos o site
                Log::error("Erro na busca $tipo: " . $e->getMessage());
                $resultados[$tipo] = [
                    'tempo_execucao' => 0,
                    'total_encontrado' => 0,
                    'resultados' => [],
                    'descricao' => 'Erro ao executar: ' . $e->getMessage(),
                    'complexidade' => 'Erro',
                    'comparacoes_realizadas' => 0
                ];
            }
            // FIM DA CORREÇÃO
        }

        // Filtra apenas resultados válidos para o gráfico não quebrar
        $resultadosValidos = array_filter($resultados, function($r) {
            return !str_contains($r['descricao'], 'Erro');
        });

        $comparacao = $this->compararPerformance($resultadosValidos);

        return view('busca.resultados', compact('resultados', 'comparacao', 'campo', 'termo', 'tiposBusca'));
    }

    private function executarBuscaSequencial(string $campo, string $termo): array
    {
        switch ($campo) {
            case 'nome': return $this->buscaSequencial->buscarPorNome($termo);
            case 'cpf': return $this->buscaSequencial->buscarPorCpf($termo);
            case 'cidade': return $this->buscaSequencial->buscarPorCidade($termo);
            case 'email': return $this->buscaSequencial->buscarPorEmail($termo);
            default: return $this->buscaSequencial->buscarPorNome($termo);
        }
    }

    private function executarBuscaIndexada(string $campo, string $termo): array
    {
        switch ($campo) {
            case 'nome': return $this->buscaIndexada->buscarPorNome($termo);
            case 'cpf': return $this->buscaIndexada->buscarPorCpf($termo);
            case 'cidade': return $this->buscaIndexada->buscarPorCidade($termo);
            case 'email': return $this->buscaIndexada->buscarPorEmail($termo);
            default: return $this->buscaIndexada->buscarPorNome($termo);
        }
    }

    private function executarBuscaHashMap(string $campo, string $termo): array
    {
        switch ($campo) {
            case 'nome': return $this->buscaHashMap->buscarPorNome($termo);
            case 'cpf': return $this->buscaHashMap->buscarPorCpf($termo);
            case 'cidade': return $this->buscaHashMap->buscarPorCidade($termo);
            case 'email': return $this->buscaHashMap->buscarPorEmail($termo);
            default: return $this->buscaHashMap->buscarPorNome($termo);
        }
    }

    private function compararPerformance(array $resultados): array
    {
        if (empty($resultados)) return [];

        $tempos = [];
        $maisRapido = null;
        $maisLento = null;

        foreach ($resultados as $tipo => $resultado) {
            $tempo = $resultado['tempo_execucao'];
            $tempos[$tipo] = $tempo;

            if ($maisRapido === null || $tempo < $tempos[$maisRapido]) $maisRapido = $tipo;
            if ($maisLento === null || $tempo > $tempos[$maisLento]) $maisLento = $tipo;
        }

        $diferencas = [];
        // Evita divisão por zero se o tempo for 0 (caso de erro ou muito rápido)
        $tempoBase = $tempos[$maisRapido] > 0 ? $tempos[$maisRapido] : 0.0000001;

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

    public function sobre() { return view('busca.sobre'); }
    
    public function limparCache() {
        $this->buscaHashMap->limparCache();
        return redirect()->back()->with('success', 'Cache limpo!');
    }

    public function estatisticas() { return response()->json(Registro::estatisticas()); }
    
    public function infoBuscas() {
        return response()->json([
            'sequencial' => BuscaSequencialService::getInfo(),
            'indexada' => BuscaIndexadaService::getInfo(),
            'hashmap' => BuscaHashMapService::getInfo(),
        ]);
    }
}
