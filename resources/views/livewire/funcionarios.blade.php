<div>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl text-teal-300 font-bold">Gerencie Funcionários</h1>

        <button wire:click="openModal()"
            class='bg-teal-500/90 hover:bg-teal-400 text-slate-950 font-semibold py-2 px-4 rounded-md shadow-lg shadow-teal-500/30 transition-colors'>+
            Adicionar funcionário</button>

    </div>

    @if (session()->has('message'))
        <div class="rounded border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200 mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if ($isModalOpen)
        <div class="fixed inset-0 top-0 left-0 w-full h-full bg-slate-950/70 backdrop-blur z-40 flex items-center justify-center px-4">
            <div class="w-full max-w-lg rounded-2xl border border-slate-800/70 bg-slate-950/85 p-6 shadow-2xl shadow-slate-950/60">
                <form wire:submit.prevent="createFuncionario">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-100" id="modal-headline">
                                    Adicionar Novo Funcionário
                                </h3>
                                <div class="mt-2">
                                    <input wire:model="nome" type="text" placeholder="Nome do funcionário"
                                        class="input-dark w-full" autocomplete="off">
                                    @error('nome') <span class="text-xs text-red-400">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-800/70 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow px-4 py-2 bg-teal-500/90 text-base font-medium text-slate-950 hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button wire:click="closeModal()" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-700/80 px-4 py-2 text-base font-medium text-slate-300 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-700/40 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif



    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mt-10">

        <!-- Card 1: Obras em andamento -->
        <div class="surface-card p-6">
            <h3 class="text-gray-600 text-xl font-semibold">Funcionários Ativos</h3>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $funcionarios->count() }}</p>
        </div>

        <!-- Card 2: Funcionários -->
        <div class="surface-card p-6">
            <h3 class="text-slate-400 text-xl font-semibold">Total a pagar</h3>
            <p class="mt-2 text-2xl font-bold text-amber-300">R$ {{ number_format($this->totalGeralPagar, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-5">

        <ul>
            <li class="surface-card mb-5 p-6">
                <div class="grid grid-cols-3 gap-4 font-semibold text-xl text-gray-700">
                    <span>Funcionário</span>
                    <span class="text-center text-xl text-gray-700">Horas</span>
                    <span class="text-right pr-36 text-xl text-gray-700">Total a receber</span>
                </div>
            </li>
        </ul>

        <ul>
            @forelse($funcionarios as $funcionario)
                <li class="surface-card mb-5 p-6">
                    <div class="grid grid-cols-3 gap-4 font-semibold text-gray-700">
                        <span class="text-gray-900">{{ $funcionario->nome }}</span>
                        <span class="text-center text-gray-700">{{ $funcionario->total_horas ?? 0 }}h</span>
                        <div class="flex items-center justify-end text-gray-900 gap-3">
                            <span>R$ {{ number_format($funcionario->total_a_pagar ?? 0, 2, ',', '.') }}</span>

                            <img wire:click="deleteFuncionario({{ $funcionario->id }})" wire:confirm="Tem certeza que deseja excluir este funcionário?" src="{{ asset('img/excluir.png') }}" alt="Excluir funcionário" class="w-5 h-5 cursor-pointer">

                            <a href="{{ route('funcionarios.horas', ['funcionario' => $funcionario->id]) }}"
                               class="text-sm text-slate-950 bg-teal-500/90 px-3 py-1 rounded hover:bg-teal-400 transition">
                                Registrar Horas
                            </a>
                        </div>
                    </div>
                </li>
            @empty
                <li class="surface-card mb-5 p-6 text-center text-gray-600">
                    Nenhum funcionário cadastrado até o momento.
                </li>
            @endforelse
        </ul>
        
        
    </div>




</div>
