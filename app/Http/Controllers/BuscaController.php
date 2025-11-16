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
        // Carrega todos os registros e cria HashMap por CPF
        $registros = Registro::all()->keyBy('cpf');
        
        // Remove formatação do CPF se houver
        $cpfLimpo = preg_replace('/[^0-9]/', '', $termo);
        
        // Busca direta no HashMap
        $resultado = $registros->get($cpfLimpo);
        
        return $resultado ? collect([$resultado]) : collect([]);
    }

    private function calcularComparacoes($metodo, $resultados)
    {
        $total = Registro::count();

        switch ($metodo) {
            case 'sequencial':
                return $total;

            case 'indexada':
                return (int)ceil(log($total, 2));

            case 'hashmap':
                return 1;

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

        foreach (['sequencial', 'indexada', 'hashmap'] as $metodo) {
            $request->merge(['metodo' => $metodo]);
            $response = $this->buscar($request);
            $data = $response->getData(true);

            $resultados[$metodo] = [
                'tempo' => $data['tempo'],
                'total' => $data['total']
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
                'economia_indexada' => round((($tempoSequencial - $tempoIndexada) / $tempoSequencial) * 100, 2),
                'economia_hashmap' => round((($tempoSequencial - $tempoHashmap) / $tempoSequencial) * 100, 2)
            ]
        ]);
    }
}
```

---

## 🎯 **AÇÃO IMEDIATA:**

**1. Substitua o arquivo `app/Http/Controllers/BuscaController.php` pelo código acima**

**2. Commit:**
```
git add .
git commit -m "Revert to stable version"
git push
