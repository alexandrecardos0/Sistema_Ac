<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Relatórios Mensais</h1>
            <p class="text-sm text-gray-600">Histórico consolidado de entradas, saídas e métricas mês a mês.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Ano</label>
                <select wire:model="ano"
                        class="input-dark mt-1 focus:border-teal-400 focus:ring-teal-500/40">
                    <option value="">Todos</option>
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
                        @if(empty($this->mesesDisponiveis) || in_array($numeroMes, $this->mesesDisponiveis, true))
                            <option value="{{ $numeroMes }}">{{ $labelMes }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFiltros"
                    class="rounded-md border border-teal-500/30 bg-white px-3 py-2 text-sm font-semibold text-teal-700 transition hover:bg-gray-50">
                Limpar filtros
            </button>
        </div>
    </div>

    <div class="surface-section">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-gray-100">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Mês</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Obras cadastradas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Entradas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Saídas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Saldo</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody class="surface-table">
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
                        <td class="px-4 py-3 text-sm font-semibold text-rose-600">
                            R$ {{ number_format($dados['valor_saidas'] ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold {{ ($dados['saldo'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            R$ {{ number_format($dados['saldo'] ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('relatorios.show', $relatorio) }}"
                            class="inline-flex items-center rounded-lg bg-teal-500/80 px-3 py-1 text-sm font-semibold text-slate-950 transition hover:bg-teal-400">
                                Ver relatório completo
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-400">
                            Nenhum relatório gerado ainda. Acesse o dashboard e selecione um mês para gerar automaticamente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
