@extends('layouts.app')

@section('title', 'Pesquisar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="buscaApp()">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Sistema de Busca</h1>
        <p class="text-gray-600">Execute buscas e compare a performance dos algoritmos</p>
    </div>

    <!-- Card de Busca -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
        
        <form @submit.prevent="buscar()" class="space-y-6">
            
            <!-- Seleção de Tipos de Busca -->
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-list-check mr-2 text-purple-600"></i>
                    Selecione os Tipos de Busca (pelo menos 1)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                           :class="metodo === 'sequencial' ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" name="metodo" value="sequencial" x-model="metodo" class="mr-3">
                        <div class="flex-1">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-list-ol text-red-600 mr-2"></i>
                                <span class="font-semibold">Sequencial</span>
                            </div>
                            <p class="text-xs text-gray-500">Busca linear registro por registro</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                           :class="metodo === 'indexada' ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" name="metodo" value="indexada" x-model="metodo" class="mr-3">
                        <div class="flex-1">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-bolt text-blue-600 mr-2"></i>
                                <span class="font-semibold">Indexada</span>
                            </div>
                            <p class="text-xs text-gray-500">Usa índices do banco de dados</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                           :class="metodo === 'hashmap' ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" name="metodo" value="hashmap" x-model="metodo" class="mr-3">
                        <div class="flex-1">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-hashtag text-green-600 mr-2"></i>
                                <span class="font-semibold">HashMap</span>
                            </div>
                            <p class="text-xs text-gray-500">Tabela hash em memória</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Campo de Busca COM AUTOCOMPLETE -->
            <div class="relative">
                <label class="block text-gray-700 font-semibold mb-3">
                    <i class="fas fa-keyboard mr-2 text-purple-600"></i>
                    Digite o Termo de Busca
                </label>
                
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="termo" 
                        @input="buscarSugestoes()"
                        @focus="mostrarSugestoes = true"
                        @blur="setTimeout(() => mostrarSugestoes = false, 200)"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:outline-none text-lg"
                        placeholder="Ex: João, 12345678900, São Paulo..."
                        required
                    >
                    
                    <!-- Dropdown de Sugestões -->
                    <div 
                        x-show="mostrarSugestoes && sugestoes.length > 0" 
                        class="absolute z-50 w-full mt-2 bg-white border-2 border-purple-300 rounded-lg shadow-2xl max-h-96 overflow-y-auto"
                        style="display: none;"
                    >
                        <!-- Header do Dropdown -->
                        <div class="sticky top-0 bg-purple-50 px-4 py-3 border-b-2 border-purple-200">
                            <p class="text-sm font-semibold text-purple-800">
                                <i class="fas fa-list mr-2"></i>
                                <span x-text="sugestoes.length"></span> registros encontrados
                            </p>
                        </div>
                        
                        <!-- Lista de Sugestões -->
                        <template x-for="sugestao in sugestoes" :key="sugestao.id">
                            <button
                                type="button"
                                @click="selecionarSugestao(sugestao)"
                                class="w-full text-left px-4 py-4 border-b hover:bg-purple-50 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <!-- Nome -->
                                        <p class="font-bold text-gray-900 mb-1" x-text="sugestao.nome"></p>
                                        
                                        <!-- Email -->
                                        <p class="text-sm text-gray-600 mb-2">
                                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                            <span x-text="sugestao.email"></span>
                                        </p>
                                        
                                        <!-- Cidade e CPF -->
                                        <div class="flex items-center text-xs text-gray-500 space-x-4">
                                            <span>
                                                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                                <span x-text="sugestao.cidade + '/' + sugestao.estado"></span>
                                            </span>
                                            <span>
                                                <i class="fas fa-id-card text-gray-400 mr-1"></i>
                                                <span x-text="formatarCPF(sugestao.cpf)"></span>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Badge de Status -->
                                    <div class="ml-4">
                                        <span 
                                            :class="sugestao.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                            x-text="sugestao.status === 'ativo' ? 'Ativo' : 'Inativo'"
                                        ></span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                    Dica: Digite pelo menos 2 caracteres para ver sugestões
                </p>
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <button 
                    type="submit" 
                    class="flex-1 bg-purple-600 text-white py-4 rounded-lg font-semibold hover:bg-purple-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="loading"
                >
                    <i class="fas mr-2" :class="loading ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                    <span x-text="loading ? 'Buscando...' : 'Buscar'"></span>
                </button>

                <button 
                    type="button"
                    @click="comparar()"
                    class="px-8 bg-indigo-600 text-white py-4 rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="loading"
                >
                    <i class="fas fa-chart-bar mr-2"></i>
                    Comparar Todos
                </button>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    <div x-show="resultados" class="bg-white rounded-xl shadow-lg p-8" style="display: none;">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
            <i class="fas fa-list-check text-purple-600 mr-2"></i>
            Resultados da Busca
        </h3>
        
        <!-- Info do Resultado -->
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <span x-text="total"></span> registros encontrados
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        Método: <span class="font-semibold capitalize" x-text="metodoUsado"></span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-green-600" x-text="tempo + 'ms'"></p>
                    <p class="text-xs text-gray-500">Tempo de execução</p>
                </div>
            </div>
        </div>

        <!-- Tabela de Resultados -->
        <div x-show="dados.length > 0" class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Nome</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">CPF</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Cidade</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in dados" :key="item.id">
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-semibold text-gray-900" x-text="item.nome"></td>
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="item.email"></td>
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="formatarCPF(item.cpf)"></td>
                            <td class="px-4 py-3 text-gray-700" x-text="item.cidade + '/' + item.estado"></td>
                            <td class="px-4 py-3">
                                <span 
                                    :class="item.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                    x-text="item.status === 'ativo' ? 'Ativo' : 'Inativo'"
                                ></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Sem Resultados -->
        <div x-show="dados.length === 0" class="text-center py-12 text-gray-500">
            <i class="fas fa-inbox text-6xl mb-4 text-gray-300"></i>
            <p class="text-lg font-semibold">Nenhum resultado encontrado</p>
            <p class="text-sm">Tente outro termo de busca</p>
        </div>
    </div>

</div>

@push('scripts')
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
        sugestoes: [],
        mostrarSugestoes: false,
        timeoutSugestao: null,

        // Buscar sugestões em tempo real
        async buscarSugestoes() {
            if (this.termo.length < 2) {
                this.sugestoes = [];
                return;
            }

            clearTimeout(this.timeoutSugestao);

            this.timeoutSugestao = setTimeout(async () => {
                try {
                    const response = await fetch('{{ route("buscar") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            termo: this.termo,
                            metodo: 'sequencial'
                        })
                    });

                    const data = await response.json();
                    this.sugestoes = (data.resultados || []).slice(0, 10);
                    this.mostrarSugestoes = true;

                } catch (error) {
                    console.error('Erro ao buscar sugestões:', error);
                }
            }, 300);
        },

        // Selecionar sugestão
        selecionarSugestao(sugestao) {
            this.termo = sugestao.nome;
            this.sugestoes = [];
            this.mostrarSugestoes = false;
            this.buscar();
        },

        // Formatar CPF
        formatarCPF(cpf) {
            if (!cpf) return '';
            return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        },

        // Executar busca
        async buscar() {
            if (!this.termo.trim()) {
                alert('Digite um termo de busca');
                return;
            }

            this.loading = true;
            this.resultados = false;
            this.mostrarSugestoes = false;

            try {
                const response = await fetch('{{ route("buscar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

        // Comparar métodos
        async comparar() {
            if (!this.termo.trim()) {
                alert('Digite um termo de busca');
                return;
            }

            this.loading = true;

            try {
                const response = await fetch('{{ route("comparar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        termo: this.termo
                    })
                });

                const data = await response.json();
                
                alert(
                    '📊 Comparação de Performance:\n\n' +
                    '🐢 Sequencial: ' + data.resultados.sequencial.tempo + 'ms (' + data.resultados.sequencial.total + ' resultados)\n' +
                    '🚀 Indexada: ' + data.resultados.indexada.tempo + 'ms (' + data.resultados.indexada.total + ' resultados)\n' +
                    '⚡ HashMap: ' + data.resultados.hashmap.tempo + 'ms (' + data.resultados.hashmap.total + ' resultados)\n\n' +
                    '🏆 Mais rápida: ' + data.analise.mais_rapida.metodo + ' (' + data.analise.mais_rapida.tempo + 'ms)\n' +
                    '⏱️ Economia: ' + data.analise.economia_hashmap.toFixed(2) + '%'
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
@endpush

@endsection
