<div class="mx-auto max-w-6xl p-4 sm:p-6 text-slate-100">
    <div class="rounded-2xl border border-slate-800/70 bg-slate-950/70 shadow-2xl shadow-slate-950/50 ring-1 ring-teal-500/10 overflow-hidden backdrop-blur">
        <div class="space-y-4 border-b border-slate-800/60 p-4 sm:p-6">
            <div class="flex flex-wrap justify-between gap-4">
                <div>
                    <span class="inline-flex items-center rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-xs font-semibold text-teal-200">
                        Folha de Horas
                    </span>
                    <h1 class="mt-2 text-lg font-bold text-teal-300 tracking-tight">{{ $funcionario->nome }}</h1>
                    <p class="text-xs uppercase tracking-wide text-slate-300">
                        Período base: {{ $this->meses[$mes] ?? 'Mês' }} / {{ $ano }}
                    </p>
                </div>
                <div class="text-right text-sm text-slate-200">
                    <p>Total a pagar</p>
                    <p class="text-2xl font-semibold text-emerald-400">
                        R$ {{ number_format($this->valorTotalPagar, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400">
                        Horas em aberto: {{ number_format($this->totalHorasAbertas, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">Ano</label>
                    <input type="number" min="2000" max="2100" wire:model="ano"
                           class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20" />
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">Mês</label>
                            <select wire:model="mes"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20">
                                @foreach($this->meses as $index => $label)
                                    <option value="{{ $index }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 pb-1">
                            <button type="button"
                                    wire:click="navigateToPreviousMonth"
                                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-cyan-400 hover:text-cyan-200 focus:outline-none focus:ring-4 focus:ring-cyan-400/20">
                                ← Mês anterior
                            </button>
                            <button type="button"
                                    wire:click="navigateToNextMonth"
                                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-cyan-400 hover:text-cyan-200 focus:outline-none focus:ring-4 focus:ring-cyan-400/20">
                                Próximo mês →
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">Valor/hora (R$)</label>
                    <input type="text" wire:model.lazy="valorHora" placeholder="Ex.: 25,00"
                           class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 @error('valorHora') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                    @error('valorHora')
                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">Vale / Adiantamento</label>
                    <div class="space-y-2">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">
                            Informe dia e mês referentes ao vale registrado.
                        </p>
                        <div class="flex flex-col gap-2 sm:grid sm:grid-cols-[1fr_auto_auto_auto] sm:items-center sm:gap-3">
                            <input type="text" wire:model.lazy="novoValeDescricao" placeholder="Descrição (opcional)"
                                   class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 sm:col-span-1 @error('novoValeDescricao') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                            <input type="number" min="1" max="31" wire:model.lazy="novoValeDia" placeholder="Dia"
                                   class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm text-center outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 sm:w-24 @error('novoValeDia') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                            <input type="number" min="1" max="12" wire:model.lazy="novoValeMes" placeholder="Mês"
                                   class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm text-center outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 sm:w-24 @error('novoValeMes') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                            <input type="text" wire:model.lazy="novoValeValor" placeholder="Valor (R$)"
                                   class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 sm:w-36 @error('novoValeValor') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                        </div>
                        @error('novoValeDescricao')
                            <p class="text-xs text-red-300">{{ $message }}</p>
                        @enderror
                        @error('novoValeMes')
                            <p class="text-xs text-red-300">{{ $message }}</p>
                        @enderror
                        @error('novoValeDia')
                            <p class="text-xs text-red-300">{{ $message }}</p>
                        @enderror
                        @error('novoValeValor')
                            <p class="text-xs text-red-300">{{ $message }}</p>
                        @enderror
                        <button type="button"
                                wire:click="addVale"
                                wire:target="addVale"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-cyan-400/50 bg-cyan-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-cyan-200 transition hover:border-cyan-300 hover:bg-cyan-400/10 hover:text-cyan-100 focus:outline-none focus:ring-4 focus:ring-cyan-300/30 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="addVale">Adicionar vale</span>
                            <span wire:loading wire:target="addVale">Adicionando...</span>
                        </button>
                        <p class="text-xs text-slate-400">
                            Total em vales adicionados: <span class="font-semibold text-emerald-200">R$ {{ number_format($this->totalVales, 2, ',', '.') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="border border-emerald-400 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('message') }}
            </div>
        @endif

        @error('horas')
            <div class="border border-red-400 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {{ $message }}
            </div>
        @enderror

        <form class="space-y-6 p-4 sm:p-6" onsubmit="return false;">
            <div class="p-1 sm:p-0">
                <div class="mb-2 grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-300">
                    <div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div><div>Dom</div>
                </div>

                <div class="grid grid-cols-7 gap-2 sm:gap-3">
                    @foreach($cells as $cell)
                        @php
                            $weekday = $cell['weekday'] ?? 0;
                            $enabled = $cell['enabled'] ?? false;
                            $locked = $cell['locked'] ?? false;
                            $dateKey = $cell['date'];
                            $cardClasses = 'flex min-h-28 flex-col gap-2 rounded-xl border p-2 sm:p-3 transition';
                            $labelColor = 'text-slate-400';
                            $inputClasses = 'mt-auto w-full rounded-lg border bg-slate-900 px-2.5 py-2 text-sm outline-none placeholder:text-slate-500';

                            if ($weekday === 7) { // domingo
                                $cardClasses .= ' border-red-500 bg-red-900/60';
                                $labelColor = 'text-red-200';
                                $inputClasses .= ' border-red-500 placeholder:text-red-200';
                                if ($enabled) {
                                    $inputClasses .= ' focus:border-red-400 focus:ring-red-400/20';
                                }
                            } elseif ($weekday === 6) { // sábado
                                $cardClasses .= ' border-orange-500 bg-orange-900/60';
                                $labelColor = 'text-orange-200';
                                $inputClasses .= ' border-orange-500 placeholder:text-orange-200';
                                if ($enabled) {
                                    $inputClasses .= ' focus:border-orange-400 focus:ring-orange-400/20';
                                }
                            } else {
                                $cardClasses .= ' border-slate-700 bg-slate-900/60';
                                if ($enabled) {
                                    $inputClasses .= ' border-slate-700 focus:border-cyan-400 focus:ring-cyan-400/20';
                                } else {
                                    $inputClasses .= ' border-slate-700';
                                }
                            }

                            if (!empty($cell['muted'])) {
                                $cardClasses .= ' opacity-70';
                            }

                            if ($locked) {
                                $cardClasses .= ' ring-1 ring-emerald-400/30';
                                $inputClasses .= ' cursor-not-allowed opacity-80';
                            }
                        @endphp

                        <div class="{{ $cardClasses }}">
                            <div class="text-xs font-semibold uppercase tracking-wide {{ $labelColor }}">
                                <span>{{ $cell['day'] }}</span>
                                <span class="ml-1 text-[10px] text-slate-400/70">{{ \Carbon\Carbon::parse($dateKey)->translatedFormat('d/m') }}</span>
                            </div>
                            <input type="text"
                                   wire:model.lazy="horas.{{ $dateKey }}"
                                   placeholder="0,00"
                                   @if(!$enabled) disabled @endif
                                   class="{{ $inputClasses }}" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-wrap gap-3 text-xs text-slate-300">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1">
                        Horas em aberto
                        <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                            {{ number_format($this->totalHorasAbertas, 2, ',', '.') }}
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1">
                        Dias em aberto
                        <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                            {{ $this->diasPreenchidosAbertos }}
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1">
                        Vales em aberto
                        <span class="rounded-full bg-amber-400/20 px-2 py-0.5 font-semibold text-amber-200">
                            R$ {{ number_format($this->totalVales, 2, ',', '.') }}
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1">
                        Horas pagas
                        <span class="rounded-full bg-emerald-400/15 px-2 py-0.5 font-semibold text-emerald-200">
                            {{ number_format($this->totalHorasPagas, 2, ',', '.') }}
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1">
                        Dias pagos
                        <span class="rounded-full bg-emerald-400/15 px-2 py-0.5 font-semibold text-emerald-200">
                            {{ $this->totalDiasPagos }}
                        </span>
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-sm text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-orange-500"></span>
                        <span>Sábados destacados em laranja</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-red-500"></span>
                        <span>Domingos destacados em vermelho</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-emerald-400/70"></span>
                        <span>Dias pagos ficam destacados e bloqueados para edição</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-slate-500"></span>
                        <span>Dias de meses adjacentes aparecem esmaecidos, mas podem ser editados</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 border-t border-slate-800/60 pt-4 sm:grid-cols-2 sm:pt-6">
                <div class="space-y-4 rounded-xl border border-slate-800/60 bg-slate-900/40 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-100">Horas em aberto</h2>
                            <p class="text-xs text-slate-400">Salve os apontamentos e feche o pagamento quando estiver pronto.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-700/60 bg-slate-900 px-2 py-0.5 text-xs text-slate-300">
                            {{ $this->diasPreenchidosAbertos }} dia(s)
                        </span>
                    </div>

                    <dl class="grid grid-cols-1 gap-3 text-xs text-slate-300 md:grid-cols-3">
                        <div class="rounded-lg border border-slate-700 bg-slate-900/60 p-3">
                            <dt>Total de horas</dt>
                            <dd class="mt-1 text-lg font-semibold text-cyan-300">
                                {{ number_format($this->totalHorasAbertas, 2, ',', '.') }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-slate-700 bg-slate-900/60 p-3">
                            <dt>Valor estimado</dt>
                            <dd class="mt-1 text-lg font-semibold text-emerald-200">
                                R$ {{ number_format($this->valorEstimadoAberto, 2, ',', '.') }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-slate-700 bg-slate-900/60 p-3">
                            <dt>Total em vales</dt>
                            <dd class="mt-1 text-lg font-semibold text-amber-200">
                                R$ {{ number_format($this->totalVales, 2, ',', '.') }}
                            </dd>
                        </div>
                    </dl>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase tracking-wide text-slate-400">Vales adicionados</span>
                            <span class="text-xs font-medium text-amber-200">Total: R$ {{ number_format($this->totalVales, 2, ',', '.') }}</span>
                        </div>
                        @if(count($vales) > 0)
                            <ul class="space-y-2">
                                @foreach($vales as $index => $vale)
                                    @php
                                        $descricao = trim($vale['descricao'] ?? '');
                                        $valor = number_format($vale['valor'] ?? 0, 2, ',', '.');
                                        $diaVale = isset($vale['dia']) ? (int) $vale['dia'] : null;
                                        $mesVale = isset($vale['mes']) ? (int) $vale['mes'] : null;
                                        $diaTexto = $diaVale && $diaVale > 0 ? str_pad($diaVale, 2, '0', STR_PAD_LEFT) : '—';
                                        $mesTexto = $mesVale && $mesVale > 0 ? str_pad($mesVale, 2, '0', STR_PAD_LEFT) : '—';
                                        $diaMesFormatado = $diaTexto . '/' . $mesTexto;
                                    @endphp
                                    <li class="flex items-start justify-between gap-3 rounded-lg border border-slate-800 bg-slate-900/60 p-3 text-sm text-slate-200">
                                        <div class="space-y-1">
                                            <p class="font-semibold text-emerald-200">
                                                R$ {{ $valor }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                {{ $descricao !== '' ? $descricao : 'Sem descrição' }}
                                            </p>
                                            <p class="text-[11px] uppercase tracking-wide text-amber-200">
                                                Dia/Mês: {{ $diaMesFormatado }}
                                            </p>
                                        </div>
                                        <button type="button"
                                                wire:click="removeVale({{ $index }})"
                                                class="rounded-lg border border-red-500/40 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-red-200 transition hover:border-red-400 hover:text-red-100 focus:outline-none focus:ring-4 focus:ring-red-400/20">
                                            Remover
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="rounded-lg border border-dashed border-slate-700 bg-slate-900/40 p-3 text-xs text-slate-400">
                                Nenhum vale adicionado para este período em aberto.
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button"
                                wire:click="saveHoras"
                                wire:target="saveHoras"
                                wire:loading.attr="disabled"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-cyan-400/60 bg-transparent px-4 py-2.5 text-sm font-semibold text-cyan-200 transition hover:border-cyan-300 hover:text-cyan-100 focus:outline-none focus:ring-4 focus:ring-cyan-300/40 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveHoras">Salvar horas</span>
                            <span wire:loading wire:target="saveHoras">Salvando...</span>
                        </button>
                        <button type="button"
                                wire:click="adicionarPagamento"
                                wire:target="adicionarPagamento"
                                wire:loading.attr="disabled"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-300/40 disabled:cursor-not-allowed disabled:opacity-60"
                                @if($this->totalHorasAbertas <= 0) disabled @endif>
                            <span wire:loading.remove wire:target="adicionarPagamento">Adicionar pagamento</span>
                            <span wire:loading wire:target="adicionarPagamento">Registrando...</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-slate-800/60 bg-slate-900/40 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-100">Pagamentos registrados</h2>
                            <p class="text-xs text-slate-400">Pagamentos podem abranger qualquer faixa de datas.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-100">
                            {{ count($pagamentos) }} registro(s)
                        </span>
                    </div>

                    @forelse($pagamentos as $pagamento)
                        @php
                            $inicio = $pagamento['periodo_inicio'] ?? null;
                            $fim = $pagamento['periodo_fim'] ?? null;
                            $inicioFormatado = $inicio ? \Carbon\Carbon::parse($inicio)->translatedFormat('d M Y') : '—';
                            $fimFormatado = $fim ? \Carbon\Carbon::parse($fim)->translatedFormat('d M Y') : '—';
                            $valesDetalhes = $pagamento['vales_detalhes'] ?? [];
                            $valeTotalFormatado = number_format($pagamento['vale'] ?? 0, 2, ',', '.');
                        @endphp
                        <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/5 p-4 text-sm text-slate-200">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-emerald-200">{{ $pagamento['label'] }}</h3>
                                    <button type="button"
                                            wire:click="exportarPagamento({{ $pagamento['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="exportarPagamento"
                                            class="inline-flex items-center gap-1 rounded-lg border border-emerald-400/50 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-100 transition hover:bg-emerald-500/20 focus:outline-none focus:ring-4 focus:ring-emerald-400/30 disabled:opacity-60">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 8h6m-6 4h6m-7 8h8a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-4.586a2 2 0 0 0-1.414.586l-4 4A2 2 0 0 0 6 9.414V18a2 2 0 0 0 2 2z" />
                                        </svg>
                                        Exportar
                                    </button>
                                </div>
                                <span class="text-xs text-slate-400">
                                    {{ $inicioFormatado }} – {{ $fimFormatado }}
                                </span>
                            </div>

                            <dl class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-slate-400">Horas</dt>
                                    <dd class="text-base font-semibold text-emerald-100">
                                        {{ number_format($pagamento['total_horas'], 2, ',', '.') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-slate-400">Dias</dt>
                                    <dd class="text-base font-semibold text-emerald-100">
                                        {{ $pagamento['quantidade_dias'] }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-slate-400">Valor/hora</dt>
                                    <dd class="text-base font-semibold text-emerald-100">
                                        R$ {{ number_format($pagamento['valor_hora'], 2, ',', '.') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-slate-400">Total de vales</dt>
                                    <dd class="text-base font-semibold text-emerald-100">
                                        R$ {{ $valeTotalFormatado }}
                                    </dd>
                                </div>
                            </dl>

                            <div class="mt-3 space-y-2">
                                <span class="text-xs uppercase tracking-wide text-slate-400">Detalhes dos vales</span>
                                @if(count($valesDetalhes) > 0)
                                    <ul class="space-y-2">
                                        @foreach($valesDetalhes as $vale)
                                            @php
                                                $descricaoVale = trim($vale['descricao'] ?? '');
                                                $valorVale = number_format($vale['valor'] ?? 0, 2, ',', '.');
                                                $diaVale = isset($vale['dia']) ? (int) $vale['dia'] : null;
                                                $mesVale = isset($vale['mes']) ? (int) $vale['mes'] : null;
                                                $diaTexto = $diaVale && $diaVale > 0 ? str_pad($diaVale, 2, '0', STR_PAD_LEFT) : '—';
                                                $mesTexto = $mesVale && $mesVale > 0 ? str_pad($mesVale, 2, '0', STR_PAD_LEFT) : '—';
                                                $diaMesTexto = $diaTexto . '/' . $mesTexto;
                                            @endphp
                                            <li class="rounded-lg border border-emerald-400/30 bg-emerald-500/10 px-3 py-2 text-xs text-slate-200">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span>{{ $descricaoVale !== '' ? $descricaoVale : 'Sem descrição' }}</span>
                                                    <span class="font-semibold text-emerald-100">R$ {{ $valorVale }}</span>
                                                </div>
                                                <p class="mt-1 text-[11px] uppercase tracking-wide text-emerald-200">
                                                    Dia/Mês: {{ $diaMesTexto }}
                                                </p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="rounded-lg border border-dashed border-emerald-400/30 bg-emerald-500/5 px-3 py-2 text-xs text-emerald-100/80">
                                        Nenhum vale vinculado a este pagamento.
                                    </p>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-xs uppercase tracking-wide text-slate-400">Total pago</span>
                                <span class="text-lg font-semibold text-emerald-200">
                                    R$ {{ number_format($pagamento['total'], 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-slate-800 bg-slate-900/40 p-4 text-sm text-slate-300">
                            Nenhum pagamento registrado para este período.
                        </p>
                    @endforelse
                </div>
            </div>
        </form>
    </div>
</div>
