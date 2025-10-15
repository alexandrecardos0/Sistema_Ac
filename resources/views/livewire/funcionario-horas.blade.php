<div class="mx-auto max-w-6xl p-4 sm:p-6 text-slate-100">
    <div class="rounded-2xl border border-slate-800/60 bg-white shadow-xl ring-1 ring-white/5 overflow-hidden">
        <div class="space-y-4 border-b border-slate-800/60 p-4 sm:p-6">
            <div class="flex flex-wrap justify-between gap-4">
                <div>
                    <span class="inline-flex items-center rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-semibold text-slate-900">
                        Folha de Horas
                    </span>
                    <h1 class="mt-2 text-lg font-bold text-red-500 tracking-tight">{{ $funcionario->nome }}</h1>
                    <p class="text-xs uppercase tracking-wide text-black">
                        Período: {{ $this->meses[$mes] ?? 'Mês' }} / {{ $ano }}
                    </p>
                </div>
                <div class="text-right text-sm text-slate-200">
                    <p>Total calculado</p>
                    <p class="text-2xl font-semibold text-green-500">
                        R$ {{ number_format($this->valorFinal, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-black">Ano</label>
                    <input type="number" min="2000" max="2100" wire:model="ano"
                           class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-black">Mês</label>
                    <select wire:model="mes"
                            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20">
                        @foreach($this->meses as $index => $label)
                            <option value="{{ $index }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-black">Valor/hora (R$)</label>
                    <input type="text" wire:model.lazy="valorHora" placeholder="Ex.: 25,00"
                           class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 @error('valorHora') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                    @error('valorHora')
                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                    @enderror
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
                <div class="mb-2 grid grid-cols-7 gap-2 text-center text-red-300 text-xs font-semibold uppercase tracking-wide text-slate-200">
                    <div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sab</div><div>Dom</div>
                </div>

                <div class="grid grid-cols-7 gap-2 sm:gap-3">
                    @foreach($cells as $cell)
                        @php
                            $weekday = $cell['weekday'] ?? 0;
                            $enabled = $cell['enabled'] ?? false;
                            $cardClasses = 'flex min-h-28 flex-col gap-2 rounded-xl border p-2 sm:p-3';
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
                                $cardClasses .= ' border-slate-800/60 bg-slate-900/60';
                                $inputClasses .= ' border-slate-700';
                                if ($enabled) {
                                    $inputClasses .= ' focus:border-cyan-400 focus:ring-cyan-400/20';
                                }
                            }

                            if ($cell['muted']) {
                                $cardClasses .= ' opacity-40';
                            }

                            if (! $enabled) {
                                $cardClasses .= ' opacity-70';
                                $inputClasses .= ' cursor-not-allowed';
                            }
                        @endphp
                        <div wire:key="cal-{{ $cell['date'] }}"
                             class="{{ $cardClasses }}">
                            <div class="text-[11px] font-bold uppercase tracking-wide {{ $labelColor }}">
                                {{ str_pad($cell['day'], 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <input type="text"
                                   @if($cell['muted'] || ! $enabled) disabled @endif
                                   @if(!$cell['muted'] && $enabled)
                                       wire:model.lazy="horas.{{ $cell['day'] }}"
                                   @endif
                                   placeholder="Ex.: 8 ou 8h30"
                                   class="{{ $inputClasses }} @error('horas.' . $cell['day']) border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 border-t border-slate-800/60 pt-4 sm:grid-cols-2 sm:pt-6">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1 text-slate-300">
                            Horas no mês
                            <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                                {{ number_format($this->totalHorasMes, 2, ',', '.') }}
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1 text-slate-300">
                            Dias com apontamento
                            <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                                {{ $this->diasPreenchidosMes }}
                            </span>
                        </span>
                    </div>

                    <p class="text-xs text-slate-400">
                        Registre as horas normalmente durante o mês. Ao fechar a 1ª quinzena, os dias quitados ficam bloqueados e os demais passam a compor a 2ª quinzena.
                    </p>
                </div>

                <div class="space-y-2 text-sm text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-orange-500"></span>
                        <span>Sábados destacados em laranja</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-red-500"></span>
                        <span>Domingos destacados em vermelho</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-3 w-3 rounded-full bg-slate-500"></span>
                        <span>Dias bloqueados ficam com aparência esmaecida</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 border-t border-slate-800/60 pt-4 sm:grid-cols-2 sm:pt-6">
                <div class="space-y-4 rounded-xl border border-slate-800/60 bg-slate-900/40 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-100">1ª Quinzena</h2>
                            <p class="text-xs text-slate-400">
                                @if($registroPrimeira)
                                    {{ count((array) ($registroPrimeira->dias_selecionados ?? [])) }} dia(s) bloqueado(s) neste período.
                                @else
                                    Inclui todos os dias registrados até fechar o pagamento.
                                @endif
                            </p>
                        </div>
                        @if($registroPrimeira)
                            <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs font-semibold text-emerald-200">
                                Pago
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1 text-slate-300">
                            Horas
                            <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                                {{ number_format($this->totalHorasPrimeira, 2, ',', '.') }}
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1 text-slate-300">
                            Dias
                            <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                                {{ $this->diasPreenchidosPrimeira }}
                            </span>
                        </span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">Vale / Adiantamento (R$)</label>
                        <input type="text" wire:model.lazy="vales.1" placeholder="Ex.: 200,00"
                               @if($registroPrimeira) disabled @endif
                               class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 @error('vales.1') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror @if($registroPrimeira) opacity-70 cursor-not-allowed @endif" />
                        @error('vales.1')
                            <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="text-xs uppercase tracking-wide text-slate-400">Valor a pagar</span>
                        <p class="text-2xl font-extrabold leading-tight text-emerald-200">
                            R$ {{ number_format($this->valorFinalPrimeira, 2, ',', '.') }}
                        </p>
                    </div>

                    <button type="button"
                            wire:click="pagarPrimeiraQuinzena"
                            wire:target="pagarPrimeiraQuinzena"
                            wire:loading.attr="disabled"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-300/40 disabled:cursor-not-allowed disabled:opacity-50"
                            @if($registroPrimeira || $this->totalHorasPrimeira <= 0) disabled @endif>
                        <span wire:loading.remove wire:target="pagarPrimeiraQuinzena">Pagar 1ª quinzena</span>
                        <span wire:loading wire:target="pagarPrimeiraQuinzena">Registrando...</span>
                    </button>
                </div>

                <div class="space-y-4 rounded-xl border border-slate-800/60 bg-slate-900/40 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-100">2ª Quinzena</h2>
                            <p class="text-xs text-slate-400">
                                @if($registroPrimeira)
                                    Dias restantes após o primeiro pagamento.
                                @else
                                    Disponível somente após pagar a 1ª quinzena.
                                @endif
                            </p>
                        </div>
                        @if($registroSegunda)
                            <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs font-semibold text-emerald-200">
                                Pago
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1 text-slate-300">
                            Horas
                            <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                                {{ number_format($this->totalHorasSegunda, 2, ',', '.') }}
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/70 bg-slate-900 px-2.5 py-1 text-slate-300">
                            Dias
                            <span class="rounded-full bg-cyan-400/15 px-2 py-0.5 font-semibold text-cyan-300">
                                {{ $this->diasPreenchidosSegunda }}
                            </span>
                        </span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">Vale / Adiantamento (R$)</label>
                        <input type="text" wire:model.lazy="vales.2" placeholder="Ex.: 150,00"
                               @if($registroSegunda || ! $registroPrimeira) disabled @endif
                               class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 @error('vales.2') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror @if($registroSegunda || ! $registroPrimeira) opacity-70 cursor-not-allowed @endif" />
                        @error('vales.2')
                            <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                        @if(! $registroPrimeira)
                            <p class="mt-1 text-xs text-slate-500">Preenchimento liberado após pagar a 1ª quinzena.</p>
                        @endif
                    </div>

                    <div>
                        <span class="text-xs uppercase tracking-wide text-slate-400">Valor a pagar</span>
                        <p class="text-2xl font-extrabold leading-tight text-emerald-200">
                            R$ {{ number_format($this->valorFinalSegunda, 2, ',', '.') }}
                        </p>
                    </div>

                    <button type="button"
                            wire:click="pagarSegundaQuinzena"
                            wire:target="pagarSegundaQuinzena"
                            wire:loading.attr="disabled"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-300/40 disabled:cursor-not-allowed disabled:opacity-50"
                            @if($registroSegunda || ! $registroPrimeira || $this->totalHorasSegunda <= 0) disabled @endif>
                        <span wire:loading.remove wire:target="pagarSegundaQuinzena">Pagar 2ª quinzena</span>
                        <span wire:loading wire:target="pagarSegundaQuinzena">Registrando...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
