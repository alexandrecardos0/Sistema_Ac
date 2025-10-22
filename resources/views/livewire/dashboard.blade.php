<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-3xl font-bold text-teal-300">Dashboard</h2>
            <p class="text-sm text-slate-400">Visão consolidada por mês das entradas e saídas.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-300">Ano</label>
                <input type="number" min="2000" max="2100" wire:model.live="ano"
                       class="input-dark mt-1 w-28 focus:border-teal-400 focus:ring-teal-500/40" />
            </div>
            <div> 
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-300">Mês</label>
                <select wire:model.live="mes"
                        class="input-dark mt-1 focus:border-teal-400 focus:ring-teal-500/40">
                    @foreach($this->meses as $indice => $label)
                        <option class='bg-slate-950 text-slate-100' value="{{ $indice }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Funcionários ativos</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $totalFuncionarios }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $funcionariosNoMes }} contratado(s) no mês selecionado</p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Obras cadastradas no mês</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $obrasNoMes }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $obrasEmAndamento }} obra(s) em andamento no total</p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Entradas — Obras</h3>
            <p class="mt-2 text-3xl font-bold text-emerald-400">R$ {{ number_format($valorObrasMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Valor efetivamente recebido no mês</p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saídas — Funcionários</h3>
            <p class="mt-2 text-3xl font-bold text-rose-400">R$ {{ number_format($valorFuncionariosMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Pagamentos registrados no mês (folha de horas)</p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saídas — Avarias</h3>
            <p class="mt-2 text-3xl font-bold text-rose-400">R$ {{ number_format($valorAvariasMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Custos de conserto lançados no mês</p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saídas — Compras</h3>
            <p class="mt-2 text-3xl font-bold text-rose-400">R$ {{ number_format($valorComprasMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">
                Materiais comprados no mês. <a href="{{ route('comprar') }}" class="text-teal-300 hover:text-teal-200 underline decoration-dotted">Ver compras</a>
            </p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saídas — Veículos</h3>
            <p class="mt-2 text-3xl font-bold text-rose-400">R$ {{ number_format($valorVeiculosMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">
                Gastos de gasolina e manutenção. <a href="{{ route('gastos.veiculos') }}" class="text-teal-300 hover:text-teal-200 underline decoration-dotted">Detalhes</a>
            </p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total de saídas</h3>
            <p class="mt-2 text-3xl font-bold text-rose-500">R$ {{ number_format($valorSaidasMes, 2, ',', '.') }}</p>
        </div>

        <div class="surface-card p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saldo do mês</h3>
            <p class="mt-2 text-3xl font-bold {{ $saldoMes >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                R$ {{ number_format($saldoMes, 2, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Entradas menos saídas no período</p>
        </div>
    </div>
</div>
