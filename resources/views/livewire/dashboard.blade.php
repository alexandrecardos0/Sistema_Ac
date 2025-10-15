<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-3xl font-bold text-red-500">Dashboard</h2>
            <p class="text-sm text-gray-500">Visão consolidada por mês das entradas e saídas.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-red-500">Ano</label>
                <input type="number" min="2000" max="2100" wire:model.live="ano"
                       class="mt-1 w-28 rounded-md border border-red-500 px-3 py-1.5 text-sm text-red-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
            </div>
            <div> 
                <label class="text-xs font-semibold uppercase tracking-wide text-red-500">Mês</label>
                <select wire:model.live="mes"
                        class="mt-1 rounded-md border border-red-500 px-3 py-1.5 text-sm text-red-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    @foreach($this->meses as $indice => $label)
                        <option class='bg-black text-red-500 border-red-500' value="{{ $indice }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Funcionários ativos</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalFuncionarios }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $funcionariosNoMes }} contratado(s) no mês selecionado</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Obras cadastradas no mês</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $obrasNoMes }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $obrasEmAndamento }} obra(s) em andamento no total</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Entradas — Obras</h3>
            <p class="mt-2 text-3xl font-bold text-emerald-600">R$ {{ number_format($valorObrasMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">Valor efetivamente recebido no mês</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Saídas — Funcionários</h3>
            <p class="mt-2 text-3xl font-bold text-red-600">R$ {{ number_format($valorFuncionariosMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">Pagamentos registrados no mês (folha de horas)</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Saídas — Avarias</h3>
            <p class="mt-2 text-3xl font-bold text-red-600">R$ {{ number_format($valorAvariasMes, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">Custos de conserto lançados no mês</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total de saídas</h3>
            <p class="mt-2 text-3xl font-bold text-red-700">R$ {{ number_format($valorSaidasMes, 2, ',', '.') }}</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Saldo do mês</h3>
            <p class="mt-2 text-3xl font-bold {{ $saldoMes >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                R$ {{ number_format($saldoMes, 2, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Entradas menos saídas no período</p>
        </div>
    </div>
</div>
