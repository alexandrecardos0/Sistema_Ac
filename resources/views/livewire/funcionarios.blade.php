<div>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl text-red-500 font-bold">Gerencie Funcionários</h1>

        <button wire:click="openModal()"
            class='bg-green-500 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transitions-colors'>+
            Adicionar funcionário</button>

    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if ($isModalOpen)
        <div class="fixed inset-0 top-0 left-0 w-full h-full bg-black bg-opacity-50 z-40 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg p-6 z-50 w-full max-w-lg">
                <form wire:submit.prevent="createFuncionario">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                    Adicionar Novo Funcionário
                                </h3>
                                <div class="mt-2">
                                    <input wire:model="nome" type="text" placeholder="Nome do funcionário"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    @error('nome') <span class="text-red-500">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button wire:click="closeModal()" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif



    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mt-10">

        <!-- Card 1: Obras em andamento -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-gray-500 text-xl font-semibold">Funcionários Ativos</h3>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $funcionarios->count() }}</p>
        </div>

        <!-- Card 2: Funcionários -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-gray-500 text-xl font-semibold">Total a pagar</h3>
            <p class="mt-2 text-2xl font-bold text-yellow-500">R$ {{ number_format($this->totalGeralPagar, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-5">

        <ul>
            <li class="bg-white shadow rounded-lg p-6 mb-5">
                <div class="grid grid-cols-3 gap-4 font-semibold text-xl text-black">
                    <span>Funcionário</span>
                    <span class="text-center text-xl text-blue-700">Horas</span>
                    <span class="text-right pr-36 text-xl text-yellow-500">Total a receber</span>
                </div>
            </li>
        </ul>

        <ul>
            @forelse($funcionarios as $funcionario)
                <li class="bg-white shadow rounded-lg p-6 mb-5">
                    <div class="grid grid-cols-3 gap-4 font-semibold text-gray-700">
                        <span>{{ $funcionario->nome }}</span>
                        <span class="text-center text-blue-700">{{ $funcionario->total_horas ?? 0 }}h</span>
                        <div class="flex items-center justify-end text-yellow-500 gap-3">
                            <span>R$ {{ number_format($funcionario->total_a_pagar ?? 0, 2, ',', '.') }}</span>
        
                            <img wire:click="deleteFuncionario({{ $funcionario->id }})" wire:confirm="Tem certeza que deseja excluir este funcionário?" src="{{ asset('img/excluir.png') }}" alt="Excluir funcionário" class="w-5 h-5 cursor-pointer">
        
                            <a href="{{ route('funcionarios.horas', ['funcionario' => $funcionario->id]) }}"
                               class="text-sm text-white bg-blue-500 px-3 py-1 rounded hover:bg-blue-600">
                                Registrar Horas
                            </a>
                        </div>
                    </div>
                </li>
            @empty
                <li class="bg-white shadow rounded-lg p-6 mb-5 text-center text-gray-500">
                    Nenhum funcionário cadastrado até o momento.
                </li>
            @endforelse
        </ul>
        
        
    </div>




</div>
