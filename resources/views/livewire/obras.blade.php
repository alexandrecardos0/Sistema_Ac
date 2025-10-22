<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Gerencie suas obras</h1>
            <p class="text-sm text-slate-400">Cadastre, acompanhe e atualize os valores e progresso financeiro de cada obra.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-md bg-teal-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-teal-500/30 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
            <span class="text-lg leading-none">+</span>
            Adicionar obra
        </button>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-slate-400">Total de obras</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $this->totalObras }}</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-slate-400">Obras concluídas</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $this->obrasConcluidas }}</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-slate-400">Obras em andamento</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">{{ $this->obrasEmAndamento }}</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-slate-400">Total a receber</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">
                R$ {{ number_format($this->totalReceber, 2, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Valor ainda pendente de recebimento</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-slate-400">Total recebido</h3>
            <p class="mt-2 text-3xl font-bold text-emerald-400">
                R$ {{ number_format($this->totalRecebido, 2, ',', '.') }}
            </p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-slate-400">Valor total das obras</h3>
            <p class="mt-2 text-3xl font-bold text-slate-100">
                R$ {{ number_format($this->valorTotal, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="surface-section">
        <table class="min-w-full divide-y divide-slate-800/60">
            <thead class="bg-slate-950/60">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Obra</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Status</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Valor total</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Total recebido</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Total a receber</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Ações</th>
                </tr>
            </thead>
            <tbody class="surface-table">
                @forelse($obras as $obra)
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-100">
                            {{ $obra->nome }}
                            @if($obra->descricao_display)
                                <p class="mt-1 text-xs font-normal text-slate-400">{{ $obra->descricao_display }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-300">
                            @php
                                $statusClasses = match ($obra->status_key) {
                                    \App\Models\Obra::STATUS_ANDAMENTO => 'border border-sky-500/30 bg-sky-500/15 text-sky-300',
                                    \App\Models\Obra::STATUS_CONCLUIDA => 'border border-emerald-500/30 bg-emerald-500/15 text-emerald-300',
                                    default => 'border border-rose-500/30 bg-rose-500/15 text-rose-300',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                {{ $obra->status_display }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-100">
                            R$ {{ number_format((float) $obra->valor_total_display, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-400">
                            R$ {{ number_format((float) $obra->valor_recebido_display, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-amber-300">
                            R$ {{ number_format((float) $obra->valor_pendente_display, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <div class="flex justify-end gap-3">
                                <button wire:click="openEditModal({{ $obra->id }})"
                                        class="inline-flex items-center gap-2 rounded-md bg-sky-500/90 px-3 py-1 text-sm font-semibold text-slate-950 shadow hover:bg-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400/40">
                                    <span class="text-base leading-none" aria-hidden="true">&#9998;</span>
                                    Atualizar valores
                                </button>
                                <button wire:click="deleteObra({{ $obra->id }})"
                                        wire:confirm="Tem certeza que deseja excluir esta obra?"
                                        class="flex items-center gap-2 rounded-md border border-rose-500/40 px-3 py-1 text-sm font-semibold text-rose-300 transition hover:bg-rose-500/10">
                                    <img src="{{ asset('img/excluir.png') }}" alt="Excluir" class="h-4 w-4">
                                    Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-950/40">
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">
                            Nenhuma obra cadastrada ainda. Clique em “Adicionar obra” para iniciar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/70 px-4 backdrop-blur">
            <div class="w-full max-w-2xl rounded-2xl border border-slate-800/70 bg-slate-950/85 p-6 shadow-2xl shadow-slate-950/60">
                <h2 class="text-xl font-semibold text-slate-100">Cadastrar nova obra</h2>

                <form wire:submit.prevent="createObra" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-300">Nome da obra</label>
                            <input type="text" wire:model.defer="nome"
                                   class="input-dark mt-1 w-full">
                            @error('nome')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($this->supportsStatus)
                            <div>
                                <label class="text-sm font-medium text-slate-300">Status</label>
                                <select wire:model.defer="status"
                                        class="input-dark mt-1 w-full">
                                    @foreach($this->statuses as $value => $label)
                                        <option class="bg-slate-950 text-slate-100" value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div>
                                <label class="text-sm font-medium text-slate-300">Status</label>
                                <div class="mt-1 rounded-md border border-dashed border-slate-700/80 px-3 py-2 text-sm text-slate-400">
                                    Status não disponível para esta base de dados.
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-slate-300">Horas trabalhadas</label>
                            <input type="text" wire:model.defer="horasTrabalhadas" placeholder="Ex.: 80"
                                   class="input-dark mt-1 w-full">
                            @error('horasTrabalhadas')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-300">Valor total (R$)</label>
                            <input type="text" wire:model.defer="valorReceber" placeholder="Ex.: 5.780,00"
                                   class="input-dark mt-1 w-full">
                            @error('valorReceber')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-300">Endereço / Local</label>
                            <input type="text" wire:model.defer="endereco" placeholder="Ex.: Rua das Flores, 123"
                                   class="input-dark mt-1 w-full">
                            @error('endereco')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-300">Descrição</label>
                        <textarea wire:model.defer="descricao" rows="3"
                                  class="input-dark mt-1 w-full"
                                  placeholder="Informe detalhes relevantes sobre a obra"></textarea>
                        @error('descricao')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeCreateModal"
                                class="rounded-md border border-slate-700/80 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-900">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-md bg-teal-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-teal-500/30 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                            Salvar obra
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showEditModal && $obraBeingEdited)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/70 px-4 backdrop-blur">
            <div class="w-full max-w-md rounded-2xl border border-slate-800/70 bg-slate-950/85 p-6 shadow-2xl shadow-slate-950/60">
                <h2 class="text-xl font-semibold text-slate-100">
                    Atualizar valores — {{ $obraBeingEdited->nome }}
                </h2>

                <form wire:submit.prevent="updateValores" class="mt-4 space-y-4">
                    <div class="rounded-xl border border-slate-800/70 bg-slate-950/60 px-3 py-2 text-sm text-slate-300">
                        <p><span class="font-semibold text-slate-100">Valor total:</span> R$ {{ number_format($obraBeingEdited->valor_total_display ?? 0, 2, ',', '.') }}</p>
                        <p><span class="font-semibold text-slate-100">Valor já recebido:</span> R$ {{ number_format($obraBeingEdited->valor_recebido_display ?? 0, 2, ',', '.') }}</p>
                        <p><span class="font-semibold text-slate-100">Valor pendente:</span> R$ {{ number_format($obraBeingEdited->valor_pendente_display ?? 0, 2, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-300">Valor recebido (R$)</label>
                        <input type="text" wire:model.defer="valorRecebidoEdit"
                               class="input-dark mt-1 w-full">
                        @error('valorRecebidoEdit')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($this->supportsStatus)
                        <div>
                            <label class="text-sm font-medium text-slate-300">Status da obra</label>
                            <select wire:model.defer="statusEdit"
                                    class="input-dark mt-1 w-full">
                                @foreach($this->statuses as $key => $label)
                                    <option class="bg-slate-950 text-slate-100" value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeEditModal"
                                class="rounded-md border border-slate-700/80 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-900">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-md bg-sky-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-sky-500/30 transition hover:bg-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400/40">
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
