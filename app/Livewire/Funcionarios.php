<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use App\Models\Funcionario;

class Funcionarios extends Component
{
    public Collection $funcionarios;
    public $isModalOpen = false;
    public $nome;

    public function mount()
    {
        $this->funcionarios = collect();
        $this->loadFuncionarios();
    }

    public function loadFuncionarios()
    {
        $this->funcionarios = Funcionario::with(['registroHoras' => function ($query) {
                $query->orderByDesc('created_at');
            }])
            ->get()
            ->map(function (Funcionario $funcionario) {
                $valorHoraAtual = null;
                $totalAberto = 0.0;
                $totalHoraspagas = 0.0;

                foreach ($funcionario->registroHoras as $registro) {
                    $status = $registro->status ?? 'closed';
                    $valorHoraRegistro = $registro->valor_hora ?? null;
                    if ($valorHoraRegistro) {
                        $valorHoraAtual = (float) $valorHoraRegistro;
                    }

                    if ($status === 'draft') {
                        $dias = (array) ($registro->dias_selecionados ?? []);
                        $horas = 0.0;
                        foreach ($dias as $valor) {
                            if (is_array($valor)) {
                                $valor = $valor['horas'] ?? ($valor['valor'] ?? ($valor['total'] ?? 0));
                            }
                            $numero = (float) $valor;
                            if ($numero > 0) {
                                $horas += $numero;
                            }
                        }
                        $valesDetalhes = is_array($registro->vales_detalhes ?? null)
                            ? $registro->vales_detalhes
                            : [];
                        $totalVales = array_sum(array_map(function ($vale) {
                            $valor = $vale['valor'] ?? 0;
                            return is_numeric($valor) ? (float) $valor : 0.0;
                        }, $valesDetalhes));

                        $totalAberto += max(0, $horas * ($valorHoraAtual ?? 0) - $totalVales);
                        continue;
                    }

                    $totalHoraspagas += (float) ($registro->total ?? 0);
                }

                $funcionario->total_horas = round(
                    $funcionario->registroHoras->reduce(function ($carry, $registro) {
                        $dias = (array) ($registro->dias_selecionados ?? []);
                        foreach ($dias as $valor) {
                            if (is_array($valor)) {
                                $valor = $valor['horas'] ?? ($valor['valor'] ?? ($valor['total'] ?? 0));
                            }
                            $numero = (float) $valor;
                            if ($numero > 0) {
                                $carry += $numero;
                            }
                        }
                        return $carry;
                    }, 0.0),
                    2
                );

                $funcionario->total_pago = round($totalHoraspagas, 2);
                $funcionario->total_aberto = round($totalAberto, 2);
                $funcionario->total_a_pagar = round($totalHoraspagas + $totalAberto, 2);

                return $funcionario;
            })
            ->values();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function createFuncionario()
    {
        $this->validate([
            'nome' => 'required|string|max:255',
        ]);

        Funcionario::create([
            'nome' => $this->nome,
        ]);

        session()->flash('message', 'Funcionário criado com sucesso.');

        $this->closeModal();
        $this->loadFuncionarios(); // Refresh the list with calculated totals
        $this->nome = '';
    }

    public function getTotalGeralPagarProperty()
    {
        return round(
            $this->funcionarios->sum(function ($funcionario) {
                return $funcionario->total_a_pagar ?? 0;
            }),
            2
        );
    }

    public function getTotalGeralPagoProperty()
    {
        return round(
            $this->funcionarios->sum(function ($funcionario) {
                return $funcionario->total_pago ?? 0;
            }),
            2
        );
    }

    public function getTotalGeralAbertoProperty()
    {
        return round(
            $this->funcionarios->sum(function ($funcionario) {
                return $funcionario->total_aberto ?? 0;
            }),
            2
        );
    }

    public function deleteFuncionario($id)
    {
        $funcionario = Funcionario::find($id);
        if ($funcionario) {
            $funcionario->delete();
            session()->flash('message', 'Funcionário excluído com sucesso.');
            $this->loadFuncionarios();
        }
    }

    public function render()
    {
        return view('livewire.funcionarios')->layout('layouts.app');
    }
}
