<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-red-500">Avarias de Materiais</h1>
            <p class="text-sm text-gray-500">Registre e acompanhe avarias e custos de conserto.</p>
        </div>
        <button wire:click="openModal"
                class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <span class="text-lg leading-none">+</span>
            Adicionar avaria
        </button>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md border border-emerald-500/40 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Material</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Valor do conserto</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Descrição</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Registrado em</th>
                    <th scope="col" class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($avarias as $avaria)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $avaria->material }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            R$ {{ number_format((float) $avaria->valor_conserto, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $avaria->descricao ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">
                            {{ optional($avaria->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="delete({{ $avaria->id }})"
                                    wire:confirm="Deseja remover esta avaria?"
                                    class="text-sm font-semibold text-red-500 hover:text-red-600">
                                Remover
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                            Nenhum registro encontrado. Clique em “Adicionar avaria” para cadastrar o primeiro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($isModalOpen)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-xl font-semibold text-gray-900">Registrar avaria</h2>
                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Material</label>
                        <input type="text" wire:model.defer="material"
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        @error('material')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Valor do conserto (R$)</label>
                        <input type="text" wire:model.defer="valorConserto" placeholder="Ex.: 350,00"
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        @error('valorConserto')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Descrição</label>
                        <textarea wire:model.defer="descricao" rows="3"
                                  class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                  placeholder="Explique o que houve com o material"></textarea>
                        @error('descricao')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
