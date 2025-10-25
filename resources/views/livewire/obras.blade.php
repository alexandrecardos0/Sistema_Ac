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
            <h3 class="text-sm font-semibold text-gray-600">Total de obras</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->totalObras }}</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-gray-600">Obras concluídas</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->obrasConcluidas }}</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-gray-600">Obras em andamento</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->obrasEmAndamento }}</p>
        </div>
        <div class="surface-card p-5">
            <h3 class="text-sm font-semibold text-gray-600">Total recebido</h3>
            <p class="mt-2 text-3xl font-bold text-emerald-400">
                R$ {{ number_format($this->totalRecebido, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="surface-section">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-gray-100">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Obra</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Total recebido</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody class="surface-table">
                @forelse($obras as $obra)
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $obra->nome }}
                            @if($obra->descricao_display)
                                <p class="mt-1 text-xs font-normal text-gray-600">{{ $obra->descricao_display }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            @php
                                $statusKey = $obra->status_key ?? \Illuminate\Support\Str::slug((string) $obra->status_display, '_');
                                $statusClasses = match ($statusKey) {
                                    'finalizado', 'finalizada', \App\Models\Obra::STATUS_CONCLUIDA => 'border border-emerald-500/40 bg-emerald-500/15 text-emerald-300',
                                    'andamento', 'em_andamento', \App\Models\Obra::STATUS_ANDAMENTO => 'border border-amber-400/40 bg-amber-400/15 text-amber-300',
                                    'pausada', 'pausado' => 'border border-rose-500/40 bg-rose-500/15 text-rose-300',
                                    default => 'border border-slate-500/40 bg-slate-500/10 text-slate-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                {{ $obra->status_display }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-400">
                            R$ {{ number_format((float) $obra->valor_recebido_display, 2, ',', '.') }}
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
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400">
                            Nenhuma obra cadastrada ainda. Clique em “Adicionar obra” para iniciar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/60 px-4 backdrop-blur">
            <div class="w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-gray-900">Cadastrar nova obra</h2>

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
                                        <option value="{{ $value }}">{{ $label }}</option>
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
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
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
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/60 px-4 backdrop-blur">
            <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-gray-900">
                    Atualizar valores — {{ $obraBeingEdited->nome }}
                </h2>

                <form wire:submit.prevent="updateValores" class="mt-4 space-y-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        <p><span class="font-semibold text-gray-900">Valor total:</span> R$ {{ number_format($obraBeingEdited->valor_total_display ?? 0, 2, ',', '.') }}</p>
                        <p><span class="font-semibold text-gray-900">Valor já recebido:</span> R$ {{ number_format($obraBeingEdited->valor_recebido_display ?? 0, 2, ',', '.') }}</p>
                    </div>

                    <div class="space-y-3 rounded-xl border border-emerald-200/60 bg-emerald-50 p-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-emerald-800">Adicionar recebimento</h3>
                            <button type="button"
                                    wire:click="adicionarRecebimento"
                                    wire:loading.attr="disabled"
                                    wire:target="adicionarRecebimento"
                                    class="rounded-md bg-emerald-500/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:opacity-60">
                                Lançar
                            </button>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-medium text-emerald-900">Valor recebido (R$)</label>
                                <input type="text" wire:model.defer="novoRecebimentoValor"
                                       placeholder="Ex.: 1.500,00"
                                       class="mt-1 w-full rounded-md border border-emerald-200 bg-white px-3 py-2 text-sm text-emerald-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                @error('novoRecebimentoValor')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs font-medium text-emerald-900">Data</label>
                                <input type="date" wire:model.defer="novoRecebimentoData"
                                       class="mt-1 w-full rounded-md border border-emerald-200 bg-white px-3 py-2 text-sm text-emerald-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                @error('novoRecebimentoData')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Histórico de recebimentos</h3>
                        @if(count($recebimentos) > 0)
                            <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                                @foreach($recebimentos as $recebimento)
                                    <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                        <span class="font-medium text-gray-900">
                                            {{ $recebimento['data'] ?? 'Data não informada' }}
                                        </span>
                                        <span class="font-semibold text-emerald-600">
                                            R$ {{ number_format($recebimento['valor'], 2, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-2 text-sm text-gray-500">
                                Nenhum recebimento lançado para esta obra.
                            </p>
                        @endif
                    </div>

                    @if($this->supportsStatus)
                        <div>
                            <label class="text-sm font-medium text-slate-300">Status da obra</label>
                            <select wire:model.defer="statusEdit"
                                    class="input-dark mt-1 w-full">
                                @foreach($this->statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeEditModal"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
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
