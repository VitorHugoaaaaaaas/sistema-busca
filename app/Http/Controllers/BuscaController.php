<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BuscaController extends Controller
{
    public function buscar(Request $request)
    {
        try {
            $termo = strtoupper(trim($request->input('termo', '')));
            $metodo = $request->input('metodo', 'sequencial');

            if (empty($termo)) {
                return response()->json([
                    'erro' => 'Por favor, digite um termo de busca',
                    'resultados' => [],
                    'tempo' => 0,
                    'metodo' => $metodo,
                    'total' => 0
                ]);
            }

            $inicio = microtime(true);
            $resultados = collect([]);

            switch ($metodo) {
                case 'sequencial':
                    $resultados = $this->buscaSequencial($termo);
                    break;

                case 'indexada':
                    $resultados = $this->buscaIndexada($termo);
                    break;

                case 'hashmap':
                    $resultados = $this->buscaHashMap($termo);
                    break;

                default:
                    return response()->json([
                        'erro' => 'Método de busca inválido',
                        'resultados' => [],
                        'tempo' => 0,
                        'metodo' => $metodo,
                        'total' => 0
                    ]);
            }

            $tempo = (microtime(true) - $inicio) * 1000;

            return response()->json([
                'resultados' => $resultados->take(100)->values(),
                'tempo' => round($tempo, 4),
                'metodo' => $metodo,
                'total' => $resultados->count(),
                'comparacoes' => $this->calcularComparacoes($metodo, $resultados->count())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'erro' => 'Erro ao realizar busca: ' . $e->getMessage(),
                'resultados' => [],
                'tempo' => 0,
                'metodo' => $metodo ?? 'desconhecido',
                'total' => 0
            ], 500);
        }
    }

    private function buscaSequencial($termo)
    {
        return Registro::where(function ($query) use ($termo) {
            $query->where('nome', 'like', "%{$termo}%")
                ->orWhere('cpf', 'like', "%{$termo}%")
                ->orWhere('email', 'like', "%{$termo}%")
                ->orWhere('cidade', 'like', "%{$termo}%");
        })->get();
    }

    private function buscaIndexada($termo)
    {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $termo);

        return Registro::where(function ($query) use ($termo, $cpfLimpo) {
            if (strlen($cpfLimpo) == 11) {
                $query->where('cpf', $cpfLimpo);
            } else {
                $query->where('nome', $termo)
                    ->orWhere('email', $termo)
                    ->orWhere('cidade', $termo);
            }
        })->get();
    }

    private function buscaHashMap($termo)
    {
        try {
            // Tentar usar cache, se falhar buscar direto
            $registros = Cache::remember('todos_registros', 3600, function () {
                return Registro::all();
            });
        } catch (\Exception $e) {
            // Se cache falhar, buscar direto do banco
            $registros = Registro::all();
        }

        $cpfLimpo = preg_replace('/[^0-9]/', '', $termo);

        return $registros->filter(function ($registro) use ($termo, $cpfLimpo) {
            // Proteção contra valores null
            $nome = $registro->nome ?? '';
            $cpf = $registro->cpf ?? '';
            $email = $registro->email ?? '';
            $cidade = $registro->cidade ?? '';

            // Busca exata por CPF
            if (strlen($cpfLimpo) == 11 && $cpf == $cpfLimpo) {
                return true;
            }

            // Busca por nome (case insensitive)
            if (!empty($nome) && stripos($nome, $termo) !== false) {
                return true;
            }

            // Busca exata por email
            if (!empty($email) && strtoupper($email) == $termo) {
                return true;
            }

            // Busca por cidade
            if (!empty($cidade) && stripos($cidade, $termo) !== false) {
                return true;
            }

            return false;
        });
    }

    private function calcularComparacoes($metodo, $resultados)
    {
        try {
            $total = Registro::count();

            switch ($metodo) {
                case 'sequencial':
                    return $total;

                case 'indexada':
                    return max(1, (int)ceil(log($total, 2)));

                case 'hashmap':
                    return $resultados > 0 ? 1 : 0;

                default:
                    return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function comparar(Request $request)
    {
        try {
            $termo = strtoupper(trim($request->input('termo', '')));

            if (empty($termo)) {
                return response()->json([
                    'erro' => 'Por favor, digite um termo de busca'
                ], 400);
            }

            $resultados = [];

            foreach (['sequencial', 'indexada', 'hashmap'] as $metodo) {
                $tempRequest = new Request([
                    'termo' => $termo,
                    'metodo' => $metodo
                ]);

                $response = $this->buscar($tempRequest);
                $data = $response->getData(true);

                $resultados[$metodo] = [
                    'tempo' => $data['tempo'] ?? 0,
                    'total' => $data['total'] ?? 0
                ];
            }

            $tempoSequencial = $resultados['sequencial']['tempo'];
            $tempoIndexada = $resultados['indexada']['tempo'];
            $tempoHashmap = $resultados['hashmap']['tempo'];

            $tempos = [
                'sequencial' => $tempoSequencial,
                'indexada' => $tempoIndexada,
                'hashmap' => $tempoHashmap
            ];

            asort($tempos);
            $maisRapida = array_key_first($tempos);
            $maisLenta = array_key_last($tempos);

            return response()->json([
                'resultados' => $resultados,
                'analise' => [
                    'mais_rapida' => [
                        'metodo' => ucfirst($maisRapida),
                        'tempo' => $tempos[$maisRapida]
                    ],
                    'mais_lenta' => [
                        'metodo' => ucfirst($maisLenta),
                        'tempo' => $tempos[$maisLenta]
                    ],
                    'economia_indexada' => $tempoSequencial > 0 
                        ? round((($tempoSequencial - $tempoIndexada) / $tempoSequencial) * 100, 2)
                        : 0,
                    'economia_hashmap' => $tempoSequencial > 0
                        ? round((($tempoSequencial - $tempoHashmap) / $tempoSequencial) * 100, 2)
                        : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'erro' => 'Erro na comparação: ' . $e->getMessage()
            ], 500);
        }
    }
}
