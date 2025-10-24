<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-teal-300">Gastos com Veículos</h1>
            <p class="text-sm text-gray-600">
                Controle despesas de gasolina e manutenção da frota.
            </p>
        </div>
        <button wire:click="openModal"
                class="inline-flex items-center gap-2 rounded-md bg-teal-500/90 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-teal-500/30 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
            <span class="text-lg leading-none">+</span>
            Registrar gasto
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
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Veículo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Valor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">KM</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Descrição</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600"></th>
                </tr>
            </thead>
            <tbody class="surface-table">
                @forelse($gastos as $gasto)
                    @php
                        $labelTipo = $gasto->tipo === 'gasolina' ? 'Gasolina' : 'Manutenção';
                        $badgeClass = $gasto->tipo === 'gasolina' ? 'bg-amber-500/20 text-amber-200' : 'bg-sky-500/20 text-sky-200';
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                {{ $labelTipo }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $gasto->veiculo ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-emerald-300">
                            R$ {{ number_format((float) $gasto->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ optional($gasto->data_gasto)->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $gasto->km !== null ? number_format($gasto->km, 0, ',', '.') . ' km' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $gasto->descricao ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="delete({{ $gasto->id }})"
                                    wire:confirm="Deseja remover este gasto?"
                                    class="text-sm font-semibold text-rose-300 hover:text-rose-200">
                                Remover
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-gray-50">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-600">
                            Nenhum gasto registrado. Clique em “Registrar gasto” para adicionar uma nova entrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($isModalOpen)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/60 px-4 backdrop-blur">
            <div class="w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-gray-900">Registrar gasto de veículo</h2>
                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-300">Tipo</label>
                            <select wire:model.defer="tipo" class="input-dark mt-1 w-full">
                                <option value="gasolina">Gasolina</option>
                                <option value="manutencao">Manutenção</option>
                            </select>
                            @error('tipo')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Veículo</label>
                            <input type="text" wire:model.defer="veiculo" class="input-dark mt-1 w-full" placeholder="Ex.: Caminhão HR">
                            @error('veiculo')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Valor (R$)</label>
                            <input type="text" wire:model.defer="valor" class="input-dark mt-1 w-full" placeholder="Ex.: 450,00" required>
                            @error('valor')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Data</label>
                            <input type="date" wire:model.defer="dataGasto" class="input-dark mt-1 w-full">
                            @error('dataGasto')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-300">Quilometragem</label>
                            <input type="number" wire:model.defer="km" class="input-dark mt-1 w-full" placeholder="Opcional">
                            @error('km')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-300">Descrição</label>
                            <textarea wire:model.defer="descricao" rows="3" class="input-dark mt-1 w-full" placeholder="Detalhes do abastecimento ou manutenção"></textarea>
                            @error('descricao')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
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
