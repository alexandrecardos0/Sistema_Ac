<div class="space-y-6">
    <a href="{{ route('relatorios.index') }}" class="text-sm font-semibold text-teal-300 transition hover:text-teal-200">&larr; Voltar para lista</a>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Relatório Mensal</h1>
            <p class="text-sm text-slate-400">Resumo completo de {{ $relatorio->mes_formatado }}</p>
        </div>
        <div class="rounded-md border border-slate-800/70 bg-slate-950/70 px-4 py-2 text-sm text-slate-300 shadow">
            Gerado em {{ optional($dados['gerado_em'] ?? null)->format('d/m/Y H:i') ?? $relatorio->updated_at->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="surface-card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Funcionários ativos</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $dados['total_funcionarios'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $dados['funcionarios_novos'] ?? 0 }} contratado(s) no mês</p>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Obras cadastradas</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $dados['obras_cadastradas'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $dados['obras_em_andamento'] ?? 0 }} obra(s) em andamento</p>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Entradas — Obras</h3>
            <p class="mt-2 text-3xl font-bold text-emerald-400">R$ {{ number_format($dados['valor_obras'] ?? 0, 2, ',', '.') }}</p>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saídas — Funcionários</h3>
            <p class="mt-2 text-3xl font-bold text-rose-400">R$ {{ number_format($dados['valor_funcionarios'] ?? 0, 2, ',', '.') }}</p>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saídas — Avarias</h3>
            <p class="mt-2 text-3xl font-bold text-rose-400">R$ {{ number_format($dados['valor_avarias'] ?? 0, 2, ',', '.') }}</p>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Saldo do mês</h3>
            <p class="mt-2 text-3xl font-bold {{ ($dados['saldo'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                R$ {{ number_format($dados['saldo'] ?? 0, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="surface-card p-6">
        <h2 class="text-lg font-semibold text-slate-100">Detalhes financeiros</h2>

        <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
            <div class="rounded-xl border border-slate-800/70 bg-slate-950/60 p-4">
                <dt class="font-semibold text-slate-300">Entradas (Obras)</dt>
                <dd class="mt-1 text-emerald-400">R$ {{ number_format($dados['valor_obras'] ?? 0, 2, ',', '.') }}</dd>
            </div>
            <div class="rounded-xl border border-slate-800/70 bg-slate-950/60 p-4">
                <dt class="font-semibold text-slate-300">Saídas (Funcionários)</dt>
                <dd class="mt-1 text-rose-400">R$ {{ number_format($dados['valor_funcionarios'] ?? 0, 2, ',', '.') }}</dd>
            </div>
            <div class="rounded-xl border border-slate-800/70 bg-slate-950/60 p-4">
                <dt class="font-semibold text-slate-300">Saídas (Avarias)</dt>
                <dd class="mt-1 text-rose-400">R$ {{ number_format($dados['valor_avarias'] ?? 0, 2, ',', '.') }}</dd>
            </div>
            <div class="rounded-xl border border-slate-800/70 bg-slate-950/60 p-4">
                <dt class="font-semibold text-slate-300">Total de saídas</dt>
                <dd class="mt-1 text-rose-500">R$ {{ number_format($dados['valor_saidas'] ?? 0, 2, ',', '.') }}</dd>
            </div>
            <div class="rounded-xl border border-slate-800/70 bg-slate-950/60 p-4 sm:col-span-2">
                <dt class="font-semibold text-slate-300">Saldo final</dt>
                <dd class="mt-1 text-lg font-bold {{ ($dados['saldo'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    R$ {{ number_format($dados['saldo'] ?? 0, 2, ',', '.') }}
                </dd>
            </div>
        </dl>
    </div>

    <div class="surface-card p-6">
        <h2 class="text-lg font-semibold text-slate-100">Observações</h2>
        <p class="mt-2 text-sm text-slate-400">
            Este relatório compila automaticamente todos os dados registrados no sistema para o mês selecionado,
            incluindo obras cadastradas, pagamentos de funcionários e custos de avarias.
        </p>
    </div>
</div>
