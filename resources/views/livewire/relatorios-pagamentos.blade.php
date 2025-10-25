<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Relatório de Pagamentos</h1>
            <p class="text-sm text-gray-600">Valores pagos por funcionário organizados por mês/ano.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px] relative">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Buscar por nome</label>
                <input type="text"
                       wire:model.live.debounce.300ms="busca"
                       placeholder="Digite o nome do funcionário"
                       class="input-dark mt-1 w-full focus:border-teal-400 focus:ring-teal-500/40" />
                @if(!empty($sugestoes))
                    <ul class="absolute z-10 mt-1 w-full rounded-xl border border-slate-700 bg-slate-900/95 text-sm text-slate-100 shadow-lg">
                        @foreach($sugestoes as $index => $sugestao)
                            <li>
                                <button type="button"
                                        wire:click="selecionarSugestao({{ $index }})"
                                        class="block w-full px-3 py-2 text-left hover:bg-teal-500/20">
                                    {{ $sugestao }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Ano</label>
                <select wire:model="ano"
                        class="input-dark mt-1 focus:border-teal-400 focus:ring-teal-500/40">
                    @foreach($anosDisponiveis as $anoOption)
                        <option value="{{ $anoOption }}">{{ $anoOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Mês</label>
                <select wire:model="mes"
                        class="input-dark mt-1 focus:border-teal-400 focus:ring-teal-500/40">
                    <option value="">Todos</option>
                    @foreach($this->meses as $numeroMes => $labelMes)
                        <option value="{{ $numeroMes }}">{{ $labelMes }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFiltros"
                    class="rounded-md border border-teal-500/30 bg-white px-3 py-2 text-sm font-semibold text-teal-700 transition hover:bg-gray-50">
                Limpar filtros
            </button>
        </div>
    </div>

    @forelse($funcionarios as $funcionario)
        <div class="surface-section">
            <div class="flex flex-wrap items-center justify-between border-b border-gray-200 px-4 py-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $funcionario['nome'] }}</h2>
                    <p class="text-xs text-gray-600">
                        Total de pagamentos registrados:
                        <span class="font-semibold text-emerald-600">
                            R$ {{ number_format($funcionario['total_recebido'], 2, ',', '.') }}
                        </span>
                    </p>
                </div>
                <div class="text-right text-xs text-gray-500">
                    Referência: {{ $this->mes ? $this->meses[$this->mes] . '/' . $ano : $ano }}
                </div>
            </div>

            <div class="px-4 py-3">
                <ul class="divide-y divide-gray-200">
                    @forelse($funcionario['pagamentos'] as $pagamento)
                        <li class="flex items-center justify-between py-2 text-sm text-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-600"></span>
                                <span>{{ $pagamento['data']->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="font-semibold text-emerald-600">
                                R$ {{ number_format($pagamento['valor'], 2, ',', '.') }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Nenhum pagamento registrado neste período.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @empty
        <div class="surface-card p-6 text-center text-sm text-gray-600">
            Nenhum pagamento encontrado para os filtros selecionados.
        </div>
    @endforelse
</div>
