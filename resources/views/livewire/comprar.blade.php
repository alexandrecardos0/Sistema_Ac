<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Compras de Materiais</h1>
            <p class="text-sm text-slate-400">Registre os materiais adquiridos e acompanhe os custos do mês.</p>
        </div>
        <button wire:click="openModal"
                class="inline-flex items-center gap-2 rounded-md bg-teal-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-teal-500/30 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
            <span class="text-lg leading-none">+</span>
            Registrar compra
        </button>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="surface-section">
        <table class="min-w-full divide-y divide-slate-800/60">
            <thead class="bg-slate-950/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Material</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Quantidade</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Valor total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Fornecedor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Observações</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400"></th>
                </tr>
            </thead>
            <tbody class="surface-table">
                @forelse($compras as $compra)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-slate-100">{{ $compra->material }}</td>
                        <td class="px-4 py-3 text-sm text-slate-300">{{ $compra->quantidade }}</td>
                        <td class="px-4 py-3 text-sm text-emerald-300">
                            R$ {{ number_format((float) $compra->valor_total, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-300">{{ $compra->fornecedor ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-300">
                            {{ optional($compra->data_compra)->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-400">
                            {{ $compra->observacoes ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="delete({{ $compra->id }})"
                                    wire:confirm="Deseja remover esta compra?"
                                    class="text-sm font-semibold text-rose-300 hover:text-rose-200">
                                Remover
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-950/40">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-400">
                            Nenhuma compra registrada ainda. Clique em “Registrar compra” para adicionar a primeira.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($isModalOpen)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/70 px-4 backdrop-blur">
            <div class="w-full max-w-2xl rounded-2xl border border-slate-800/70 bg-slate-950/85 p-6 shadow-2xl shadow-slate-950/60">
                <h2 class="text-xl font-semibold text-slate-100">Registrar compra</h2>
                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-300">Material</label>
                            <input type="text" wire:model.defer="material" class="input-dark mt-1 w-full" required>
                            @error('material')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Quantidade</label>
                            <input type="number" min="1" step="1" wire:model.defer="quantidade" class="input-dark mt-1 w-full">
                            @error('quantidade')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Valor total (R$)</label>
                            <input type="text" wire:model.defer="valorTotal" placeholder="Ex.: 1.250,00" class="input-dark mt-1 w-full">
                            @error('valorTotal')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Data da compra</label>
                            <input type="date" wire:model.defer="dataCompra" class="input-dark mt-1 w-full">
                            @error('dataCompra')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Fornecedor</label>
                            <input type="text" wire:model.defer="fornecedor" class="input-dark mt-1 w-full" placeholder="Opcional">
                            @error('fornecedor')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-300">Observações</label>
                            <textarea wire:model.defer="observacoes" rows="3" class="input-dark mt-1 w-full" placeholder="Detalhes adicionais sobre a compra"></textarea>
                            @error('observacoes')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                                class="rounded-md border border-slate-700/80 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-900">
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
