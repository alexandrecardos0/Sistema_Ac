<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-red-500">Relatórios Mensais</h1>
            <p class="text-sm text-gray-500">Histórico consolidado de entradas, saídas e métricas mês a mês.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-red-500">Ano</label>
                <select wire:model="ano"
                        class="mt-1 rounded-md border border-red-500 px-3 py-2 text-sm text-red-500 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Todos</option>
                    @foreach($anosDisponiveis as $anoOption)
                        <option value="{{ $anoOption }}">{{ $anoOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-red-500">Mês</label>
                <select wire:model="mes"
                        class="mt-1 rounded-md border border-red-500 px-3 py-2 text-sm text-red-500 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Todos</option>
                    @foreach($this->meses as $numeroMes => $labelMes)
                        @if(empty($this->mesesDisponiveis) || in_array($numeroMes, $this->mesesDisponiveis, true))
                            <option value="{{ $numeroMes }}">{{ $labelMes }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFiltros"
                    class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                Limpar filtros
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Mês</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Obras cadastradas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Entradas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Saídas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Saldo</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($relatorios as $relatorio)
                    @php
                        $dados = $relatorio->data;
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $relatorio->mes_formatado }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $dados['obras_cadastradas'] ?? 0 }} obras
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-emerald-600">
                            R$ {{ number_format($dados['valor_obras'] ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-red-600">
                            R$ {{ number_format($dados['valor_saidas'] ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold {{ ($dados['saldo'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            R$ {{ number_format($dados['saldo'] ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('relatorios.show', $relatorio) }}"
                            class="text-sm text-white bg-blue-500 px-3 py-1 rounded hover:bg-blue-600">
                                Ver relatório completo
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                            Nenhum relatório gerado ainda. Acesse o dashboard e selecione um mês para gerar automaticamente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
