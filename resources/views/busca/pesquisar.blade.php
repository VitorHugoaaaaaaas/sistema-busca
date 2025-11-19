@extends('layouts.app')

@section('title', 'Pesquisar')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="bg-white rounded-xl shadow-lg p-8 fade-in">
        
        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <div class="inline-block bg-gradient-to-r from-purple-600 to-blue-500 p-4 rounded-full mb-4">
                <i class="fas fa-search text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Sistema de Pesquisa</h1>
            <p class="text-gray-600">Selecione os tipos de busca e execute a pesquisa</p>
        </div>

        <!-- Formulário de Busca -->
        <form action="{{ route('buscar') }}" method="POST" class="space-y-6" x-data="{ tiposBusca: [], campoBusca: '' }">
            @csrf

            <!-- Seleção de Tipos de Busca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <i class="fas fa-layer-group mr-2 text-purple-600"></i>
                    Selecione os Tipos de Busca (pelo menos 1)
                </label>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Busca Sequencial -->
                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="tiposBusca.includes('sequencial') ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="checkbox" name="tipo_busca[]" value="sequencial" class="sr-only" x-model="tiposBusca">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-list-ol text-red-600 text-xl mr-2"></i>
                                <span class="font-semibold text-gray-900">Sequencial</span>
                            </div>
                            <p class="text-xs text-gray-600">Busca linear registro por registro</p>
                        </div>
                        <i class="fas fa-check-circle text-2xl transition" 
                           :class="tiposBusca.includes('sequencial') ? 'text-purple-600' : 'text-gray-300'"></i>
                    </label>

                    <!-- Busca Indexada -->
                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="tiposBusca.includes('indexada') ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="checkbox" name="tipo_busca[]" value="indexada" class="sr-only" x-model="tiposBusca">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-bolt text-blue-600 text-xl mr-2"></i>
                                <span class="font-semibold text-gray-900">Indexada</span>
                            </div>
                            <p class="text-xs text-gray-600">Usa índices do banco de dados</p>
                        </div>
                        <i class="fas fa-check-circle text-2xl transition" 
                           :class="tiposBusca.includes('indexada') ? 'text-purple-600' : 'text-gray-300'"></i>
                    </label>

                    <!-- Busca HashMap -->
                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="tiposBusca.includes('hashmap') ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="checkbox" name="tipo_busca[]" value="hashmap" class="sr-only" x-model="tiposBusca">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-hashtag text-green-600 text-xl mr-2"></i>
                                <span class="font-semibold text-gray-900">HashMap</span>
                            </div>
                            <p class="text-xs text-gray-600">Tabela hash em memória</p>
                        </div>
                        <i class="fas fa-check-circle text-2xl transition" 
                           :class="tiposBusca.includes('hashmap') ? 'text-purple-600' : 'text-gray-300'"></i>
                    </label>
                </div>

                @error('tipo_busca')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campo de Busca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <i class="fas fa-filter mr-2 text-purple-600"></i>
                    Selecione o Campo de Busca
                </label>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="campoBusca === 'nome' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="radio" name="campo_busca" value="nome" class="sr-only" x-model="campoBusca">
                        <i class="fas fa-user mr-2 text-purple-600"></i>
                        <span class="font-medium">Nome</span>
                    </label>

                    <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="campoBusca === 'cpf' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="radio" name="campo_busca" value="cpf" class="sr-only" x-model="campoBusca">
                        <i class="fas fa-id-card mr-2 text-purple-600"></i>
                        <span class="font-medium">CPF</span>
                    </label>

                    <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="campoBusca === 'cidade' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="radio" name="campo_busca" value="cidade" class="sr-only" x-model="campoBusca">
                        <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
                        <span class="font-medium">Cidade</span>
                    </label>

                    <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition hover:border-purple-600"
                           :class="campoBusca === 'email' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                        <input type="radio" name="campo_busca" value="email" class="sr-only" x-model="campoBusca">
                        <i class="fas fa-envelope mr-2 text-purple-600"></i>
                        <span class="font-medium">Email</span>
                    </label>
                </div>

                @error('campo_busca')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Termo de Busca -->
            <div>
                <label for="termo_busca" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-keyboard mr-2 text-purple-600"></i>
                    Digite o Termo de Busca
                </label>
                <input 
                    type="text" 
                    id="termo_busca" 
                    name="termo_busca" 
                    value="{{ old('termo_busca') }}"
                    placeholder="Ex: João, 12345678900, São Paulo..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"
                    required
                >
                @error('termo_busca')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <p class="mt-2 text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Dica: Digite pelo menos 2 caracteres para iniciar a busca
                </p>
            </div>

            <!-- Botão de Buscar -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <button 
                    type="submit" 
                    class="flex-1 bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-4 px-6 rounded-lg hover:from-purple-700 hover:to-blue-600 transition transform hover:scale-105 shadow-lg"
                >
                    <i class="fas fa-search mr-2"></i>
                    Executar Busca
                </button>

                <button 
                    type="reset" 
                    class="flex-1 sm:flex-initial bg-gray-200 text-gray-700 font-semibold py-4 px-6 rounded-lg hover:bg-gray-300 transition"
                    @click="tiposBusca = []; campoBusca = '';"
                >
                    <i class="fas fa-redo mr-2"></i>
                    Limpar
                </button>
            </div>
        </form>

        <!-- Informações Adicionais -->
        <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="font-semibold text-blue-900 mb-3">
                <i class="fas fa-lightbulb mr-2"></i>
                Como Funciona?
            </h3>
            <ul class="space-y-2 text-sm text-blue-800">
                <li class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-1 text-blue-600"></i>
                    <span>Selecione um ou mais tipos de busca para comparar suas performances</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-1 text-blue-600"></i>
                    <span>Escolha qual campo você quer buscar (Nome, CPF, Cidade ou Email)</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-1 text-blue-600"></i>
                    <span>Digite o termo e veja os resultados com métricas de performance</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-1 text-blue-600"></i>
                    <span>O sistema possui mais de 5.000 registros para demonstração</span>
                </li>
            </ul>
        </div>

    </div>
</div>
@endsection
