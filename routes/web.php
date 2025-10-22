<?php


use App\Livewire\Dashboard;
use App\Livewire\Funcionarios;
use App\Livewire\Obras;
use App\Livewire\FuncionarioHoras;
use App\Livewire\Materiais;
use App\Livewire\RelatoriosMensais;
use App\Livewire\RelatorioMensalShow;
use App\Livewire\RelatoriosPagamentos;
use App\Livewire\Comprar;
use App\Livewire\GastosVeiculos;
use App\Models\Funcionario;



Route::get('/', Dashboard::class)->name('dashboard');

Route::get('/funcionarios', Funcionarios::class)->name('funcionarios');

Route::get('/obras', Obras::class)->name('obras');

Route::get('/materiais', Materiais::class)->name('materiais');
Route::get('/comprar', Comprar::class)->name('comprar');
Route::get('/gastos-veiculos', GastosVeiculos::class)->name('gastos.veiculos');

Route::get('/relatorios', RelatoriosMensais::class)->name('relatorios.index');
Route::get('/relatorios/{relatorio}', RelatorioMensalShow::class)->name('relatorios.show');
Route::get('/relatorios-pagamentos', RelatoriosPagamentos::class)->name('relatorios.pagamentos');

// Rota da página single de controle de horas
Route::get('/funcionarios/{funcionario}/horas', FuncionarioHoras::class)->name('funcionarios.horas');
