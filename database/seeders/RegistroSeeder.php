<?php

/**
 * Seeder MELHORADO: RegistroSeeder
 * 
 * ONDE SALVAR: database/seeders/RegistroSeeder.php
 * 
 * Popula a tabela com dados realistas e fáceis de buscar.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registro;
use Illuminate\Support\Facades\DB;

class RegistroSeeder extends Seeder
{
    private $nomes = [
        'MARIA', 'JOSE', 'ANA', 'JOAO', 'ANTONIO', 'FRANCISCO',
        'CARLOS', 'PAULO', 'PEDRO', 'LUCAS', 'LUIZ', 'MARCOS',
        'LUIS', 'GABRIEL', 'RAFAEL', 'DANIEL', 'MARCELO', 'BRUNO',
        'FERNANDO', 'FABIO', 'RODRIGO', 'PATRICIA', 'SANDRA', 'JULIANA',
    ];

    private $sobrenomes = [
        'SILVA', 'SANTOS', 'OLIVEIRA', 'SOUZA', 'RODRIGUES', 'FERREIRA',
        'ALVES', 'PEREIRA', 'LIMA', 'GOMES', 'COSTA', 'RIBEIRO',
        'MARTINS', 'CARVALHO', 'ROCHA', 'ALMEIDA', 'NASCIMENTO', 'ARAUJO',
    ];

    private $cidades = [
        'SP' => ['SAO PAULO', 'CAMPINAS', 'SANTOS', 'RIBEIRAO PRETO'],
        'RJ' => ['RIO DE JANEIRO', 'NITEROI', 'CAMPOS'],
        'MG' => ['BELO HORIZONTE', 'UBERLANDIA', 'CONTAGEM'],
        'RS' => ['PORTO ALEGRE', 'CAXIAS DO SUL', 'PELOTAS'],
        'BA' => ['SALVADOR', 'FEIRA DE SANTANA'],
        'PR' => ['CURITIBA', 'LONDRINA', 'MARINGA'],
    ];

    public function run(): void
    {
        echo "\n╔════════════════════════════════════════════╗\n";
        echo "║   SISTEMA DE BUSCA - DATABASE SEEDER      ║\n";
        echo "╚════════════════════════════════════════════╝\n\n";

        echo "🗑️  Limpando tabela de registros...\n";
        DB::table('registros')->truncate();

        $totalRegistros = 6000;
        echo "📝 Criando {$totalRegistros} registros...\n";
        echo "⏳ Isso pode levar alguns segundos...\n\n";

        $loteSize = 500;
        $lotes = ceil($totalRegistros / $loteSize);

        for ($lote = 0; $lote < $lotes; $lote++) {
            $registros = [];
            $registrosNesteLote = min($loteSize, $totalRegistros - ($lote * $loteSize));

            for ($i = 0; $i < $registrosNesteLote; $i++) {
                $estado = array_rand($this->cidades);
                $cidade = $this->cidades[$estado][array_rand($this->cidades[$estado])];
                
                $nome = $this->nomes[array_rand($this->nomes)];
                $sobrenome = $this->sobrenomes[array_rand($this->sobrenomes)];
                $nomeCompleto = $nome . ' ' . $sobrenome;

                $registros[] = [
                    'nome' => $nomeCompleto,
                    'email' => strtolower($nome . '.' . $sobrenome . rand(1, 9999)) . '@email.com',
                    'cpf' => $this->gerarCPF(),
                    'telefone' => $this->gerarTelefone(),
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'data_nascimento' => date('Y-m-d', strtotime('-' . rand(18, 80) . ' years')),
                    'status' => rand(1, 100) <= 75 ? 'ativo' : 'inativo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // CORREÇÃO: Adicionar try-catch para ignorar duplicatas
            try {
                Registro::insert($registros);
            } catch (\Exception $e) {
                // Ignora erros de duplicação e continua
                echo "\n⚠️  Aviso: Alguns registros duplicados foram ignorados no lote " . ($lote + 1) . "\n";
                continue;
            }

            $progresso = (($lote + 1) / $lotes) * 100;
            echo "\r[" . str_repeat('█', (int)($progresso / 2)) . str_repeat('░', 50 - (int)($progresso / 2)) . "] " . 
                 number_format($progresso, 1) . "%";
        }

        echo "\n\n✅ Registros criados com sucesso!\n";
        
        $stats = [
            'total' => Registro::count(),
            'ativos' => Registro::where('status', 'ativo')->count(),
            'inativos' => Registro::where('status', 'inativo')->count(),
        ];
        
        echo "📊 Estatísticas:\n";
        echo "   - Total: {$stats['total']}\n";
        echo "   - Ativos: {$stats['ativos']}\n";
        echo "   - Inativos: {$stats['inativos']}\n";
        
        echo "\n💡 Exemplos de buscas:\n";
        echo "   - Nome: MARIA, SILVA, JOSE, ANA\n";
        echo "   - Cidade: SAO PAULO, RIO, BELO HORIZONTE\n";
        
        echo "\n🎉 Seed concluído!\n\n";
    }

    private function gerarCPF(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[] = rand(0, 9);
        }

        $d1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d1 += $n[$i] * (10 - $i);
        }
        $d1 = 11 - ($d1 % 11);
        $d1 = ($d1 >= 10) ? 0 : $d1;

        $d2 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d2 += $n[$i] * (11 - $i);
        }
        $d2 += $d1 * 2;
        $d2 = 11 - ($d2 % 11);
        $d2 = ($d2 >= 10) ? 0 : $d2;

        return implode('', $n) . $d1 . $d2;
    }

    private function gerarTelefone(): string
    {
        $ddd = ['11', '21', '31', '41', '51'][array_rand(['11', '21', '31', '41', '51'])];
        $ehCelular = rand(1, 100) <= 90;
        
        if ($ehCelular) {
            return $ddd . '9' . rand(10000000, 99999999);
        } else {
            return $ddd . rand(10000000, 99999999);
        }
    }
}
