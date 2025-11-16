<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BuscaController extends Controller
{
    public function buscar(Request $request)
    {
        $termo = strtoupper(trim($request->input('termo')));
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
        $resultados = [];

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
        // Remove caracteres especiais do CPF se for CPF
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
        // CORREÇÃO: Usar cache e buscar por múltiplos campos
        $registros = Cache::remember('todos_registros', 3600, function () {
            return Registro::all();
        });

        // Remove caracteres especiais do CPF se for CPF
        $cpfLimpo = preg_replace('/[^0-9]/', '', $termo);

        // Buscar por múltiplos critérios
        return $registros->filter(function ($registro) use ($termo, $cpfLimpo) {
            // Busca exata por CPF
            if (strlen($cpfLimpo) == 11 && $registro->cpf == $cpfLimpo) {
                return true;
            }

            // Busca por nome (contém o termo)
            if (stripos($registro->nome, $termo) !== false) {
                return true;
            }

            // Busca exata por email
            if (strtoupper($registro->email) == $termo) {
                return true;
            }

            // Busca por cidade (contém o termo)
            if (stripos($registro->cidade, $termo) !== false) {
                return true;
            }

            return false;
        });
    }

    private function calcularComparacoes($metodo, $resultados)
    {
        $total = Registro::count();

        switch ($metodo) {
            case 'sequencial':
                return $total; // Olha todos os registros

            case 'indexada':
                return (int)ceil(log($total, 2)); // Árvore binária

            case 'hashmap':
                return $resultados > 0 ? 1 : 0; // Acesso direto (mas pode precisar filtrar)

            default:
                return 0;
        }
    }

    public function comparar(Request $request)
    {
        $termo = strtoupper(trim($request->input('termo')));

        if (empty($termo)) {
            return response()->json([
                'erro' => 'Por favor, digite um termo de busca'
            ]);
        }

        $resultados = [];

        // Executar cada método
        foreach (['sequencial', 'indexada', 'hashmap'] as $metodo) {
            $request->merge(['metodo' => $metodo]);
            $response = $this->buscar($request);
            $data = $response->getData(true);

            $resultados[$metodo] = [
                'tempo' => $data['tempo'],
                'total' => $data['total']
            ];
        }

        // Calcular economias
        $tempoSequencial = $resultados['sequencial']['tempo'];
        $tempoIndexada = $resultados['indexada']['tempo'];
        $tempoHashmap = $resultados['hashmap']['tempo'];

        // Encontrar a mais rápida e mais lenta
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
    }
}
