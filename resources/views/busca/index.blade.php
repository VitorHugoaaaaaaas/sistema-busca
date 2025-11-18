@extends('layouts.app')

@section('title', 'Início')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Hero Section -->
    <div class="gradient-animated rounded-xl p-12 text-white text-center mb-12 fade-in">
        <h1 class="text-5xl font-bold mb-4">Sistema de Busca em Banco de Dados</h1>
        <p class="text-xl mb-8">Demonstração de Busca Sequencial, Indexada e HashMap</p>
        <a href="{{ route('pesquisar') }}" class="inline-block bg-white text-purple-600 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition transform hover:scale-105">
            <i class="fas fa-search mr-2"></i>Começar a Pesquisar
        </a>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white rounded-xl p-6 shadow-lg card-hover slide-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total de Registros</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-database text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg card-hover slide-in" style="animation-delay: 0.1s;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Registros Ativos</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($stats['ativos']) }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg card-hover slide-in" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Registros Inativos</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($stats['inativos']) }}</p>
                </div>
                <div class="bg-red-100 p-4 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg card-hover slide-in" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Estados</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['por_estado']->count() }}</p>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-map-marked-alt text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipos de Busca -->
    <h2 class="text-3xl font-bold text-gray-900 mb-6">
        <i class="fas fa-search text-purple-600 mr-3"></i>Tipos de Busca Implementados
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <!-- Busca Sequencial -->
        <div class="bg-white rounded-xl p-8 shadow-lg card-hover fade-in">
            <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-6 mx-auto">
                <i class="fas fa-list-ol text-red-600 text-2xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-center mb-4 text-gray-900">{{ $infoSequencial['nome'] }}</h3>
            
            <p class="text-gray-600 text-center mb-6">{{ $infoSequencial['descricao'] }}</p>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Complexidade:</span>
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ $infoSequencial['complexidade'] }}
                    </span>
                </div>
            </div>

            <div class="space-y-2 mb-6">
                <p class="text-sm font-semibold text-gray-700">Vantagens:</p>
                @foreach(array_slice($infoSequencial['vantagens'], 0, 2) as $vantagem)
                <div class="flex items-start">
                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-600">{{ $vantagem }}</span>
                </div>
                @endforeach
            </div>

            <div class="space-y-2">
                <p class="text-sm font-semibold text-gray-700">Desvantagens:</p>
                @foreach(array_slice($infoSequencial['desvantagens'], 0, 2) as $desvantagem)
                <div class="flex items-start">
                    <i class="fas fa-times text-red-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-600">{{ $desvantagem }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Busca Indexada -->
        <div class="bg-white rounded-xl p-8 shadow-lg card-hover fade-in" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-6 mx-auto">
                <i class="fas fa-bolt text-blue-600 text-2xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-center mb-4 text-gray-900">{{ $infoIndexada['nome'] }}</h3>
            
            <p class="text-gray-600 text-center mb-6">{{ $infoIndexada['descricao'] }}</p>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Complexidade:</span>
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ $infoIndexada['complexidade'] }}
                    </span>
                </div>
            </div>

            <div class="space-y-2 mb-6">
                <p class="text-sm font-semibold text-gray-700">Vantagens:</p>
                @foreach(array_slice($infoIndexada['vantagens'], 0, 2) as $vantagem)
                <div class="flex items-start">
                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-600">{{ $vantagem }}</span>
                </div>
                @endforeach
            </div>

            <div class="space-y-2">
                <p class="text-sm font-semibold text-gray-700">Desvantagens:</p>
                @foreach(array_slice($infoIndexada['desvantagens'], 0, 2) as $desvantagem)
                <div class="flex items-start">
                    <i class="fas fa-times text-red-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-600">{{ $desvantagem }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Busca HashMap -->
        <div class="bg-white rounded-xl p-8 shadow-lg card-hover fade-in" style="animation-delay: 0.4s;">
            <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-6 mx-auto">
                <i class="fas fa-hashtag text-green-600 text-2xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-center mb-4 text-gray-900">{{ $infoHashMap['nome'] }}</h3>
            
            <p class="text-gray-600 text-center mb-6">{{ $infoHashMap['descricao'] }}</p>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Complexidade:</span>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ $infoHashMap['complexidade'] }}
                    </span>
                </div>
            </div>

            <div class="space-y-2 mb-6">
                <p class="text-sm font-semibold text-gray-700">Vantagens:</p>
                @foreach(array_slice($infoHashMap['vantagens'], 0, 2) as $vantagem)
                <div class="flex items-start">
                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-600">{{ $vantagem }}</span>
                </div>
                @endforeach
            </div>

            <div class="space-y-2">
                <p class="text-sm font-semibold text-gray-700">Desvantagens:</p>
                @foreach(array_slice($infoHashMap['desvantagens'], 0, 2) as $desvantagem)
                <div class="flex items-start">
                    <i class="fas fa-times text-red-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-600">{{ $desvantagem }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-500 rounded-xl p-12 text-white text-center fade-in">
        <h2 class="text-3xl font-bold mb-4">Pronto para Começar?</h2>
        <p class="text-lg mb-8">Execute buscas e compare a performance entre os três métodos!</p>
        <a href="{{ route('pesquisar') }}" class="inline-block bg-white text-purple-600 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition transform hover:scale-105">
            <i class="fas fa-play mr-2"></i>Iniciar Pesquisa
        </a>
    </div>

</div>
@endsection
