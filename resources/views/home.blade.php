<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema de Busca em Banco de Dados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-4">Sistema de Busca em Banco de Dados</h1>
            <p class="text-xl">Demonstração de Busca Sequencial, Indexada e HashMap</p>
            
            <!-- Botão de Busca -->
            <div class="mt-8">
                <a href="#busca" class="inline-flex items-center px-8 py-4 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Começar a Pesquisar
                </a>
            </div>
        </div>
    </header>

    <!-- Estatísticas -->
    <section class="py-12 -mt-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total de Registros -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Total de Registros</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($stats['total'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Registros Ativos -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Registros Ativos</p>
                            <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['ativos'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Registros Inativos -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Registros Inativos</p>
                            <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($stats['inativos'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-red-100 p-4 rounded-full">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Estados -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Estados</p>
                            <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['estados'] }}</p>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sistema de Busca -->
    <section id="busca" class="py-16" x-data="buscaApp()">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tipos de Busca Implementados
                </h2>

                <!-- Formulário de Busca -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <form @submit.prevent="buscar()" class="space-y-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-3">Termo de Busca:</label>
                            <input 
                                type="text" 
                                x-model="termo" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:outline-none text-lg"
                                placeholder="Digite um nome, CPF, cidade..."
                                required
                            >
                            <p class="text-sm text-gray-500 mt-2">
                                💡 Exemplos: MARIA, JOSÉ, SAO PAULO, 12345678901
                            </p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-3">Método de Busca:</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-purple-50 transition"
                                       :class="metodo === 'sequencial' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                                    <input type="radio" name="metodo" value="sequencial" x-model="metodo" class="mr-3">
                                    <div>
                                        <span class="font-semibold text-gray-800">Sequencial</span>
                                        <p class="text-xs text-gray-500">Busca linear</p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-purple-50 transition"
                                       :class="metodo === 'indexada' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                                    <input type="radio" name="metodo" value="indexada" x-model="metodo" class="mr-3">
                                    <div>
                                        <span class="font-semibold text-gray-800">Indexada</span>
                                        <p class="text-xs text-gray-500">Com índices</p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-purple-50 transition"
                                       :class="metodo === 'hashmap' ? 'border-purple-600 bg-purple-50' : 'border-gray-200'">
                                    <input type="radio" name="metodo" value="hashmap" x-model="metodo" class="mr-3">
                                    <div>
                                        <span class="font-semibold text-gray-800">HashMap</span>
                                        <p class="text-xs text-gray-500">Em memória</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button 
                                type="submit" 
                                class="flex-1 bg-purple-600 text-white py-4 rounded-lg font-semibold hover:bg-purple-700 transition shadow-lg"
                                :disabled="loading"
                            >
                                <span x-show="!loading">🔍 Buscar</span>
                                <span x-show="loading">⏳ Buscando...</span>
                            </button>

                            <button 
                                type="button"
                                @click="comparar()"
                                class="px-8 bg-indigo-600 text-white py-4 rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg"
                                :disabled="loading"
                            >
                                📊 Comparar Todos
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resultados -->
                <div x-show="resultados" class="bg-white rounded-xl shadow-lg p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Resultados</h3>
                    
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <p class="text-lg">
                            <span class="font-semibold" x-text="total"></span> registros encontrados em 
                            <span class="font-bold text-green-600" x-text="tempo + 'ms'"></span>
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            Método: <span class="font-semibold capitalize" x-text="metodoUsado"></span>
                        </p>
                    </div>

                    <div x-show="dados.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left">Nome</th>
                                    <th class="px-4 py-3 text-left">Email</th>
                                    <th class="px-4 py-3 text-left">Cidade</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in dados" :key="item.id">
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3" x-text="item.nome"></td>
                                        <td class="px-4 py-3 text-sm text-gray-600" x-text="item.email"></td>
                                        <td class="px-4 py-3" x-text="item.cidade + '/' + item.estado"></td>
                                        <td class="px-4 py-3">
                                            <span :class="item.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                                  class="px-3 py-1 rounded-full text-sm font-medium"
                                                  x-text="item.status === 'ativo' ? 'Ativo' : 'Inativo'">
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="dados.length === 0" class="text-center py-8 text-gray-500">
                        Nenhum resultado encontrado para este tipo de busca
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function buscaApp() {
            return {
                termo: '',
                metodo: 'sequencial',
                loading: false,
                resultados: false,
                dados: [],
                tempo: 0,
                total: 0,
                metodoUsado: '',

                async buscar() {
                    if (!this.termo.trim()) {
                        alert('Digite um termo de busca');
                        return;
                    }

                    this.loading = true;
                    this.resultados = false;

                    try {
                        const response = await fetch('/buscar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                termo: this.termo,
                                metodo: this.metodo
                            })
                        });

                        const data = await response.json();

                        this.dados = data.resultados || [];
                        this.tempo = data.tempo || 0;
                        this.total = data.total || 0;
                        this.metodoUsado = data.metodo || this.metodo;
                        this.resultados = true;

                    } catch (error) {
                        alert('Erro ao realizar busca: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                async comparar() {
                    if (!this.termo.trim()) {
                        alert('Digite um termo de busca');
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('/comparar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                termo: this.termo
                            })
                        });

                        const data = await response.json();
                        
                        alert(`Comparação de Performance:\n\n` +
                              `Sequencial: ${data.resultados.sequencial.tempo}ms\n` +
                              `Indexada: ${data.resultados.indexada.tempo}ms\n` +
                              `HashMap: ${data.resultados.hashmap.tempo}ms\n\n` +
                              `Mais rápida: ${data.analise.mais_rapida.metodo} (${data.analise.mais_rapida.tempo}ms)`
                        );

                    } catch (error) {
                        alert('Erro ao comparar: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
