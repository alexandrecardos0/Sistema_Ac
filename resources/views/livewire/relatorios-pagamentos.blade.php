<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-red-500">Relatório de Pagamentos</h1>
            <p class="text-sm text-gray-500">Valores pagos por funcionário organizados por mês/ano.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-red-500">Ano</label>
                <select wire:model="ano"
                        class="mt-1 rounded-md border border-red-500 px-3 py-2 text-sm text-red-500 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
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
                        <option value="{{ $numeroMes }}">{{ $labelMes }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFiltros"
                    class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                Limpar filtros
            </button>
        </div>
    </div>

    @forelse($funcionarios as $funcionario)
        <div class="rounded-lg border border-gray-200 bg-white shadow">
            <div class="flex flex-wrap items-center justify-between border-b border-gray-100 px-4 py-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $funcionario['nome'] }}</h2>
                    <p class="text-xs text-gray-500">
                        Total de pagamentos registrados:
                        <span class="font-semibold text-emerald-600">
                            R$ {{ number_format($funcionario['total_recebido'], 2, ',', '.') }}
                        </span>
                    </p>
                </div>
                <div class="text-right text-xs text-gray-400">
                    Referência: {{ $this->mes ? $this->meses[$this->mes] . '/' . $ano : $ano }}
                </div>
            </div>

            <div class="px-4 py-3">
                <ul class="divide-y divide-gray-100">
                    @forelse($funcionario['pagamentos'] as $pagamento)
                        <li class="flex items-center justify-between py-2 text-sm text-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span>{{ $pagamento['data']->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="font-semibold text-emerald-600">
                                R$ {{ number_format($pagamento['valor'], 2, ',', '.') }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400">Nenhum pagamento registrado neste período.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 shadow">
            Nenhum pagamento encontrado para os filtros selecionados.
        </div>
    @endforelse
</div>
