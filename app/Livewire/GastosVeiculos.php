<?php

namespace App\Livewire;

use App\Models\GastoVeiculo;
use Illuminate\Support\Collection;
use Livewire\Component;

class GastosVeiculos extends Component
{
    public Collection $gastos;
    public bool $isModalOpen = false;

    public string $tipo = 'gasolina';
    public string $veiculo = '';
    public string $valor = '';
    public ?string $dataGasto = null;
    public string $km = '';
    public string $descricao = '';

    public function mount(): void
    {
        $this->gastos = collect();
        $this->loadGastos();
    }

    public function render()
    {
        return view('livewire.gastos-veiculos')->layout('layouts.app');
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
    }

    public function save(): void
    {
        $data = $this->validate([
            'tipo' => ['required', 'in:gasolina,manutencao'],
            'veiculo' => ['nullable', 'string', 'max:255'],
            'valor' => ['required', 'string'],
            'dataGasto' => ['nullable', 'date'],
            'km' => ['nullable', 'numeric', 'min:0'],
            'descricao' => ['nullable', 'string'],
        ], [
            'tipo.in' => 'Escolha uma opção válida.',
            'valor.required' => 'Informe o valor gasto.',
            'dataGasto.date' => 'Informe uma data válida.',
        ]);

        $valor = $this->normalizeValor($data['valor'] ?? '');
        if ($valor <= 0) {
            $this->addError('valor', 'O valor deve ser maior que zero.');
            return;
        }

        GastoVeiculo::create([
            'tipo' => $data['tipo'],
            'veiculo' => $data['veiculo'] ?: null,
            'valor' => $valor,
            'data_gasto' => $data['dataGasto'] ?: null,
            'km' => $data['km'] !== null && $data['km'] !== '' ? (int) $data['km'] : null,
            'descricao' => $data['descricao'] ?: null,
        ]);

        session()->flash('message', 'Gasto registrado com sucesso.');

        $this->closeModal();
        $this->loadGastos();
    }

    public function delete(int $id): void
    {
        $gasto = GastoVeiculo::find($id);
        if (! $gasto) {
            return;
        }

        $gasto->delete();
        session()->flash('message', 'Gasto removido.');
        $this->loadGastos();
    }

    private function loadGastos(): void
    {
        $this->gastos = GastoVeiculo::orderByDesc('data_gasto')
            ->orderByDesc('created_at')
            ->get();
    }

    private function resetForm(): void
    {
        $this->tipo = 'gasolina';
        $this->veiculo = '';
        $this->valor = '';
        $this->dataGasto = now()->toDateString();
        $this->km = '';
        $this->descricao = '';
        $this->resetErrorBag();
    }

    private function normalizeValor(?string $valor): float
    {
        if ($valor === null || trim($valor) === '') {
            return 0.0;
        }

        $clean = str_replace(["\u{00A0}", ' '], '', $valor);
        if (str_contains($clean, ',')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }

        return (float) $clean;
    }
}
