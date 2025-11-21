@extends('layouts.app')

@section('title', 'Resultados da Busca')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 fade-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Resultados da Busca</h1>
                <p class="text-gray-600">
                    <i class="fas fa-search mr-2"></i>
                    Termo buscado: <span class="font-semibold">"{{ $termo }}"</span> 
                    em <span class="font-semibold">{{ ucfirst($campo) }}</span>
                </p>
            </div>
            <a href="{{ route('pesquisar') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Nova Busca
            </a>
        </div>
    </div>

    @if(count($comparacao) > 0)
    <div class="bg-white rounded-xl shadow-lg p-8 mb-6 fade-in">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
            Comparação de Performance
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <canvas id="chartPerformance"></canvas>
            </div>

            <div class="space-y-4">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-trophy text-green-600 text-2xl mr-3"></i>
                        <h3 class="font-bold text-green-900">Mais Rápida</h3>
                    </div>
                    <p class="text-green-800 font-semibold text-lg">
                        {{ ucfirst($comparacao['mais_rapido']) }} - 
                        {{ number_format($comparacao['tempos'][$comparacao['mais_rapido']], 4) }}ms
                    </p>
                </div>

                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-hourglass-half text-red-600 text-2xl mr-3"></i>
                        <h3 class="font-bold text-red-900">Mais Lenta</h3>
                    </div>
                    <p class="text-red-800 font-semibold text-lg">
                        {{ ucfirst($comparacao['mais_lento']) }} - 
                        {{ number_format($comparacao['tempos'][$comparacao['mais_lento']], 4) }}ms
                    </p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-clock text-blue-600 text-2xl mr-3"></i>
                        <h3 class="font-bold text-blue-900">Tempo Economizado</h3>
                    </div>
                    <p class="text-blue-800 font-semibold text-lg">
                        {{ number_format($comparacao['tempo_economizado'], 4) }}ms
                    </p>
                </div>

                @foreach($comparacao['diferencas'] as $tipo => $diferenca)
                    @if($diferenca > 0)
                    <div class="bg-gray-50 border-l-4 border-gray-500 p-4 rounded">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">{{ ucfirst($tipo) }}</span>
                            <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                +{{ number_format($diferenca, 2) }}% mais lenta
                            </span>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @foreach($resultados as $tipo => $resultado)
    <div class="bg-white rounded-xl shadow-lg p-8 mb-6 fade-in" style="animation-delay: {{ $loop->index * 0.1 }}s;">
        
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                @if($tipo === 'sequencial')
                    <i class="fas fa-list-ol text-red-600 mr-2"></i>Busca Sequencial
                @elseif($tipo === 'indexada')
                    <i class="fas fa-bolt text-blue-600 mr-2"></i>Busca Indexada
                @else
                    <i class="fas fa-hashtag text-green-600 mr-2"></i>Busca HashMap
                @endif
            </h2>

            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Tempo de Execução</p>
                    <p class="text-2xl font-bold 
                        @if($tipo === 'sequencial') text-red-600
                        @elseif($tipo === 'indexada') text-blue-600
                        @else text-green-600
                        @endif">
                        <i class="fas fa-stopwatch mr-2"></i>
                        {{ number_format($resultado['tempo_execucao'], 4) }}ms
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Resultados</p>
                <p class="text-2xl font-bold text-gray-900">{{ $resultado['total_encontrado'] }}</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Complexidade</p>
                <p class="text-lg font-bold text-gray-900">{{ $resultado['complexidade'] ?? 'N/A' }}</p>
            </div>

            @if(isset($resultado['comparacoes_realizadas']))
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Comparações</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($resultado['comparacoes_realizadas']) }}</p>
            </div>
            @endif

            @if(isset($resultado['total_buckets']))
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Buckets</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($resultado['total_buckets']) }}</p>
            </div>
            @endif

            @if(isset($resultado['cache_hit']))
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Cache</p>
                <p class="text-lg font-bold {{ $resultado['cache_hit'] ? 'text-green-600' : 'text-orange-600' }}">
                    {{ $resultado['cache_hit'] ? 'HIT ✓' : 'MISS ✗' }}
                </p>
            </div>
            @endif
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <p class="text-blue-900">
                <i class="fas fa-info-circle mr-2"></i>
                {{ $resultado['descricao'] }}
            </p>
        </div>

        @if($resultado['total_encontrado'] > 0)
        <div class="overflow-x-auto">
            <h3 class="font-semibold text-gray-900 mb-3">
                <i class="fas fa-table mr-2"></i>
                Primeiros {{ min(10, $resultado['total_encontrado']) }} Resultados
            </h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach(array_slice($resultado['resultados'], 0, 10) as $registro)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $registro->id ?? $registro['id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $registro->nome ?? $registro['nome'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $registro->email ?? $registro['email'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registro->cidade ?? $registro['cidade'] }}/{{ $registro->estado ?? $registro['estado'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ ($registro->status ?? $registro['status']) === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($registro->status ?? $registro['status']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if($resultado['total_encontrado'] > 10)
            <p class="mt-4 text-sm text-gray-500 text-center">
                <i class="fas fa-info-circle mr-1"></i>
                Exibindo 10 de {{ $resultado['total_encontrado'] }} resultados encontrados
            </p>
            @endif
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-3"></i>
            <p>Nenhum resultado encontrado para este tipo de busca</p>
        </div>
        @endif
    </div>
    @endforeach

</div>

@endsection

@section('extra-js')
<script>
// Dados para o gráfico
const labels = [@foreach($comparacao['tempos'] ?? [] as $tipo => $tempo)"{{ ucfirst($tipo)}}", @endforeach];
const data = [@foreach($comparacao['tempos'] ?? [] as $tempo){{ $tempo }}, @endforeach];

// Cores para cada tipo
const cores = {
    'Sequencial': 'rgba(239, 68, 68, 0.8)',
    'Indexada': 'rgba(59, 130, 246, 0.8)',
    'Hashmap': 'rgba(34, 197, 94, 0.8)'
};

const backgroundColors = labels.map(label => cores[label]);

// Configuração do gráfico
const ctx = document.getElementById('chartPerformance').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Tempo de Execução (ms)',
            data: data,
            backgroundColor: backgroundColors,
            borderColor: backgroundColors,
            borderWidth: 2,
            borderRadius: 8,
            minBarLength: 10, // CORREÇÃO: Define um tamanho mínimo para a barra aparecer
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Tempo: ' + context.parsed.y.toFixed(4) + 'ms';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + 'ms';
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
@endsection
