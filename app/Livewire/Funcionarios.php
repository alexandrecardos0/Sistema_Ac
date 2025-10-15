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
        $this->funcionarios = Funcionario::with('registroHoras')
            ->get()
            ->map(function (Funcionario $funcionario) {
                $totalHoras = $funcionario->registroHoras->reduce(
                    function (float $carry, $registro) {
                        $dias = $registro->dias_selecionados ?? [];
                        if (!is_array($dias)) {
                            return $carry;
                        }

                        $horasNoMes = 0.0;
                        foreach ($dias as $valor) {
                            if (is_array($valor)) {
                                $valor = $valor['horas'] ?? ($valor['valor'] ?? ($valor['total'] ?? 0));
                            }

                            $numero = (float) str_replace(',', '.', (string) $valor);
                            if (is_finite($numero) && $numero > 0) {
                                $horasNoMes += $numero;
                            }
                        }

                        return $carry + $horasNoMes;
                    },
                    0.0
                );

                $funcionario->total_horas = round($totalHoras, 2);
                $funcionario->total_a_pagar = round(
                    (float) $funcionario->registroHoras->sum('total'),
                    2
                );

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
        return $this->funcionarios->sum(function ($funcionario) {
            return $funcionario->total_a_pagar ?? 0;
        });
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
