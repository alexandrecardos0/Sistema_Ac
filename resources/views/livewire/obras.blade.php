<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-red-500">Gerencie suas obras</h1>
            <p class="text-sm text-gray-500">Cadastre, acompanhe e atualize os valores e progresso financeiro de cada obra.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-300">
            <span class="text-lg leading-none">+</span>
            Adicionar obra
        </button>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md border border-emerald-500/40 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg bg-white p-5 shadow">
            <h3 class="text-sm font-semibold text-gray-500">Total de obras</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->totalObras }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow">
            <h3 class="text-sm font-semibold text-gray-500">Obras concluídas</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->obrasConcluidas }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow">
            <h3 class="text-sm font-semibold text-gray-500">Obras em andamento</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->obrasEmAndamento }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow">
            <h3 class="text-sm font-semibold text-gray-500">Total a receber</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                R$ {{ number_format($this->totalReceber, 2, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Valor ainda pendente de recebimento</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow">
            <h3 class="text-sm font-semibold text-gray-500">Total recebido</h3>
            <p class="mt-2 text-3xl font-bold text-emerald-600">
                R$ {{ number_format($this->totalRecebido, 2, ',', '.') }}
            </p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow">
            <h3 class="text-sm font-semibold text-gray-500">Valor total das obras</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                R$ {{ number_format($this->valorTotal, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Obra</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Valor total</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total recebido</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total a receber</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($obras as $obra)
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $obra->nome }}
                            @if($obra->descricao_display)
                                <p class="mt-1 text-xs font-normal text-gray-500">{{ $obra->descricao_display }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            @php
                                $statusClasses = match ($obra->status_key) {
                                    \App\Models\Obra::STATUS_ANDAMENTO => 'bg-blue-100 text-blue-700 border border-blue-200',
                                    \App\Models\Obra::STATUS_CONCLUIDA => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                    default => 'bg-red-100 text-red-700 border border-red-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                {{ $obra->status_display }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-700">
                            R$ {{ number_format((float) $obra->valor_total_display, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-600">
                            R$ {{ number_format((float) $obra->valor_recebido_display, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-yellow-600">
                            R$ {{ number_format((float) $obra->valor_pendente_display, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <div class="flex justify-end gap-3">
                                <button wire:click="openEditModal({{ $obra->id }})"
                                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-1 text-sm font-semibold text-white shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span class="text-base leading-none" aria-hidden="true">&#9998;</span>
                                    Atualizar valores
                                </button>
                                <button wire:click="deleteObra({{ $obra->id }})"
                                        wire:confirm="Tem certeza que deseja excluir esta obra?"
                                        class="flex items-center gap-2 rounded-md border border-red-200 px-3 py-1 text-sm font-semibold text-red-600 hover:bg-red-50">
                                    <img src="{{ asset('img/excluir.png') }}" alt="Excluir" class="h-4 w-4">
                                    Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                            Nenhuma obra cadastrada ainda. Clique em “Adicionar obra” para iniciar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-xl font-semibold text-gray-900">Cadastrar nova obra</h2>

                <form wire:submit.prevent="createObra" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Nome da obra</label>
                            <input type="text" wire:model.defer="nome"
                                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
                            @error('nome')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($this->supportsStatus)
                            <div>
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <select wire:model.defer="status"
                                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
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
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1 rounded-md border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-500">
                                    Status não disponível para esta base de dados.
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-gray-700">Horas trabalhadas</label>
                            <input type="text" wire:model.defer="horasTrabalhadas" placeholder="Ex.: 80"
                                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
                            @error('horasTrabalhadas')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Valor total (R$)</label>
                            <input type="text" wire:model.defer="valorReceber" placeholder="Ex.: 5.780,00"
                                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
                            @error('valorReceber')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Endereço / Local</label>
                            <input type="text" wire:model.defer="endereco" placeholder="Ex.: Rua das Flores, 123"
                                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
                            @error('endereco')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Descrição</label>
                        <textarea wire:model.defer="descricao" rows="3"
                                  class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200"
                                  placeholder="Informe detalhes relevantes sobre a obra"></textarea>
                        @error('descricao')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeCreateModal"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-300">
                            Salvar obra
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showEditModal && $obraBeingEdited)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-xl font-semibold text-gray-900">
                    Atualizar valores — {{ $obraBeingEdited->nome }}
                </h2>

                <form wire:submit.prevent="updateValores" class="mt-4 space-y-4">
                    <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
                        <p><span class="font-semibold text-gray-800">Valor total:</span> R$ {{ number_format($obraBeingEdited->valor_total_display ?? 0, 2, ',', '.') }}</p>
                        <p><span class="font-semibold text-gray-800">Valor já recebido:</span> R$ {{ number_format($obraBeingEdited->valor_recebido_display ?? 0, 2, ',', '.') }}</p>
                        <p><span class="font-semibold text-gray-800">Valor pendente:</span> R$ {{ number_format($obraBeingEdited->valor_pendente_display ?? 0, 2, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Valor recebido (R$)</label>
                        <input type="text" wire:model.defer="valorRecebidoEdit"
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        @error('valorRecebidoEdit')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($this->supportsStatus)
                        <div>
                            <label class="text-sm font-medium text-gray-700">Status da obra</label>
                            <select wire:model.defer="statusEdit"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                @foreach($this->statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeEditModal"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
