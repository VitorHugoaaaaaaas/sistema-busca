<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;

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
        // Carrega TODOS os registros UMA vez (fica em memória)
        $todosRegistros = Registro::all();
        
        // Remove caracteres especiais do CPF se for CPF
        $cpfLimpo = preg_replace('/[^0-9]/', '', $termo);
        
        // BUSCA em memória (super rápido!)
        $resultados = $todosRegistros->filter(function ($registro) use ($termo, $cpfLimpo) {
            // 1. Busca por CPF (exato)
            if (strlen($cpfLimpo) == 11 && $registro->cpf == $cpfLimpo) {
                return true;
            }
            
            // 2. Busca por NOME (contém)
            if (str_contains($registro->nome, $termo)) {
                return true;
            }
            
            // 3. Busca por EMAIL (exato)
            if (strtoupper($registro->email) == strtolower($termo)) {
                return true;
            }
            
            // 4. Busca por CIDADE (contém)
            if (str_contains($registro->cidade, $termo)) {
                return true;
            }
            
            return false;
        });
        
        return $resultados;
    }

    private function calcularComparacoes($metodo, $resultados)
    {
        $total = Registro::count();

        switch ($metodo) {
            case 'sequencial':
                return $total; // Olha todos os registros

            case 'indexada':
                return max(1, (int)ceil(log($total, 2))); // Árvore binária

            case 'hashmap':
                return $resultados > 0 ? 1 : 0; // Acesso direto em memória

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

        // Executar cada método de busca
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

        // Calcular análise de performance
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
    }
}
