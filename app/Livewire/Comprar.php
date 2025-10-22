<?php

namespace App\Livewire;

use App\Models\CompraMaterial;
use Illuminate\Support\Collection;
use Livewire\Component;

class Comprar extends Component
{
    public Collection $compras;
    public bool $isModalOpen = false;

    public string $material = '';
    public string $quantidade = '1';
    public string $valorTotal = '';
    public ?string $dataCompra = null;
    public string $fornecedor = '';
    public string $observacoes = '';

    public function mount(): void
    {
        $this->compras = collect();
        $this->loadCompras();
    }

    public function render()
    {
        return view('livewire.comprar')->layout('layouts.app');
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
            'material' => ['required', 'string', 'max:255'],
            'quantidade' => ['nullable', 'numeric', 'min:1'],
            'valorTotal' => ['nullable', 'string'],
            'dataCompra' => ['nullable', 'date'],
            'fornecedor' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string'],
        ], [
            'dataCompra.date' => 'Informe uma data válida.',
        ]);

        $valor = $this->normalizeValor($data['valorTotal'] ?? '');
        $quantidade = (int) ($data['quantidade'] ?? 1);
        if ($quantidade < 1) {
            $quantidade = 1;
        }

        CompraMaterial::create([
            'material' => $data['material'],
            'quantidade' => $quantidade,
            'valor_total' => $valor,
            'data_compra' => $data['dataCompra'] ?: null,
            'fornecedor' => $data['fornecedor'] ?: null,
            'observacoes' => $data['observacoes'] ?: null,
        ]);

        session()->flash('message', 'Compra registrada com sucesso.');

        $this->closeModal();
        $this->loadCompras();
    }

    public function delete(int $id): void
    {
        $compra = CompraMaterial::find($id);
        if (! $compra) {
            return;
        }

        $compra->delete();
        session()->flash('message', 'Registro removido.');
        $this->loadCompras();
    }

    private function loadCompras(): void
    {
        $this->compras = CompraMaterial::orderByDesc('data_compra')
            ->orderByDesc('created_at')
            ->get();
    }

    private function resetForm(): void
    {
        $this->material = '';
        $this->quantidade = '1';
        $this->valorTotal = '';
        $this->dataCompra = now()->toDateString();
        $this->fornecedor = '';
        $this->observacoes = '';
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
