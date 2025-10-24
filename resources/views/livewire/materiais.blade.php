<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Avarias de Materiais</h1>
            <p class="text-sm text-slate-400">Registre e acompanhe avarias e custos de conserto.</p>
        </div>
        <button wire:click="openModal"
                class="inline-flex items-center gap-2 rounded-md bg-teal-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-teal-500/30 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
            <span class="text-lg leading-none">+</span>
            Adicionar avaria
        </button>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="surface-section">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-gray-100">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Material</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Valor do conserto</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Descrição</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Registrado em</th>
                    <th scope="col" class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="surface-table">
                @forelse($avarias as $avaria)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $avaria->material }}</td>
                        <td class="px-4 py-3 text-sm text-slate-300">
                            R$ {{ number_format((float) $avaria->valor_conserto, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-400">
                            {{ $avaria->descricao ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">
                            {{ optional($avaria->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="delete({{ $avaria->id }})"
                                    wire:confirm="Deseja remover esta avaria?"
                                    class="text-sm font-semibold text-rose-300 hover:text-rose-200">
                                Remover
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-gray-50">
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-600">
                            Nenhum registro encontrado. Clique em “Adicionar avaria” para cadastrar o primeiro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($isModalOpen)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/60 px-4 backdrop-blur">
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-gray-900">Registrar avaria</h2>
                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-300">Material</label>
                        <input type="text" wire:model.defer="material"
                               class="input-dark mt-1 w-full">
                        @error('material')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-300">Valor do conserto (R$)</label>
                        <input type="text" wire:model.defer="valorConserto" placeholder="Ex.: 350,00"
                               class="input-dark mt-1 w-full">
                        @error('valorConserto')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-300">Descrição</label>
                        <textarea wire:model.defer="descricao" rows="3"
                                  class="input-dark mt-1 w-full"
                                  placeholder="Explique o que houve com o material"></textarea>
                        @error('descricao')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-md bg-teal-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-teal-500/30 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
