<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuscaSequencialService;
use App\Services\BuscaIndexadaService;
use App\Services\BuscaHashMapService;
use App\Models\Registro;

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

        return view('busca.index', compact(
            'stats',
            'infoSequencial',
            'infoIndexada',
            'infoHashMap'
        ));
    }

    public function pesquisar()
    {
        return view('busca.pesquisar');
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

        $comparacao = $this->compararPerformance($resultados);

        return view('busca.resultados', compact(
            'resultados',
            'comparacao',
            'campo',
            'termo',
            'tiposBusca'
        ));
    }

    public function autocomplete(Request $request)
    {
        $termo = $request->input('termo', '');

        if (strlen($termo) < 2) {
            return response()->json([
                'success' => true,
                'resultados' => [],
                'total' => 0
            ]);
        }

        $resultados = Registro::where(function($query) use ($termo) {
            $query->where('nome', 'LIKE', '%' . $termo . '%')
                  ->orWhere('email', 'LIKE', '%' . $termo . '%')
                  ->orWhere('cpf', 'LIKE', '%' . $termo . '%')
                  ->orWhere('cidade', 'LIKE', '%' . $termo . '%');
        })
        ->select('id', 'nome', 'email', 'cpf', 'cidade', 'estado', 'status')
        ->limit(10)
        ->get();

        return response()->json([
            'success' => true,
            'resultados' => $resultados,
            'total' => $resultados->count()
        ]);
    }

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
     * 🔧 CORREÇÃO AQUI — adicionamos suporte ao campo EMAIL
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
            case 'email':   // ✔ AGORA FUNCIONA
                return $this->buscaHashMap->buscarPorEmail($termo);
            default:
                return $this->buscaHashMap->buscarPorNome($termo);
        }
    }

    private function compararPerformance(array $resultados): array
    {
        if (empty($resultados)) {
            return [];
        }

        $tempos = [];
        $maisRapido = null;
        $maisLento = null;

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

    public function sobre()
    {
        return view('busca.sobre');
    }

    public function limparCache()
    {
        $this->buscaHashMap->limparCache();
        return redirect()->back()->with('success', 'Cache dos HashMaps limpo com sucesso!');
    }

    public function estatisticas()
    {
        return response()->json(Registro::estatisticas());
    }

    public function infoBuscas()
    {
        return response()->json([
            'sequencial' => BuscaSequencialService::getInfo(),
            'indexada' => BuscaIndexadaService::getInfo(),
            'hashmap' => BuscaHashMapService::getInfo(),
        ]);
    }
}
