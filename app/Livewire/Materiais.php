<?php

namespace App\Livewire;

use App\Models\AvariaMaterial;
use Illuminate\Support\Collection;
use Livewire\Component;

class Materiais extends Component
{
    public Collection $avarias;
    public bool $isModalOpen = false;

    public string $material = '';
    public string $valorConserto = '';
    public string $descricao = '';

    public function mount(): void
    {
        $this->avarias = collect();
        $this->loadAvarias();
    }

    public function render()
    {
        return view('livewire.materiais')->layout('layouts.app');
    }

    public function openModal(): void
    {
        $this->resetInput();
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
            'valorConserto' => ['nullable', 'string'],
            'descricao' => ['nullable', 'string'],
        ]);

        $valor = $this->normalizeValor($data['valorConserto'] ?? '');

        AvariaMaterial::create([
            'material' => $data['material'],
            'valor_conserto' => $valor,
            'descricao' => $data['descricao'] ?: null,
        ]);

        session()->flash('message', 'Avaria registrada com sucesso.');

        $this->closeModal();
        $this->loadAvarias();
    }

    public function delete(int $id): void
    {
        $avaria = AvariaMaterial::find($id);
        if ($avaria) {
            $avaria->delete();
            session()->flash('message', 'Registro removido.');
            $this->loadAvarias();
        }
    }

    private function loadAvarias(): void
    {
        $this->avarias = AvariaMaterial::orderByDesc('created_at')->get();
    }

    private function resetInput(): void
    {
        $this->material = '';
        $this->valorConserto = '';
        $this->descricao = '';
    }

    private function normalizeValor(?string $valor): float
    {
        if ($valor === null || $valor === '') {
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
