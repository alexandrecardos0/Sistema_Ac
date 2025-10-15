<?php

namespace App\Livewire;

use App\Models\RelatorioMensal;
use Livewire\Component;

class RelatoriosMensais extends Component
{
    public ?int $ano = null;
    public ?int $mes = null;

    /** @var array<int> */
    public array $anosDisponiveis = [];

    public function mount(): void
    {
        $this->anosDisponiveis = RelatorioMensal::select('ano')
            ->distinct()
            ->orderByDesc('ano')
            ->pluck('ano')
            ->all();

        $this->ano = $this->anosDisponiveis[0] ?? null;
    }

    public function updatedAno($value): void
    {
        $this->ano = $value ? (int) $value : null;
        $this->mes = null;
    }

    public function updatedMes($value): void
    {
        $this->mes = $value ? (int) $value : null;
    }

    public function resetFiltros(): void
    {
        $this->ano = $this->anosDisponiveis[0] ?? null;
        $this->mes = null;
    }

    public function getMesesProperty(): array
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
    }

    /**
     * @return array<int>
     */
    public function getMesesDisponiveisProperty(): array
    {
        $query = RelatorioMensal::query();
        if ($this->ano) {
            $query->where('ano', $this->ano);
        }

        return $query->select('mes')
            ->distinct()
            ->orderBy('mes')
            ->pluck('mes')
            ->all();
    }

    public function render()
    {
        $query = RelatorioMensal::query()
            ->orderByDesc('ano')
            ->orderByDesc('mes');

        if ($this->ano) {
            $query->where('ano', $this->ano);
        }

        if ($this->mes) {
            $query->where('mes', $this->mes);
        }

        $relatorios = $query->get();

        return view('livewire.relatorios-mensais', [
            'relatorios' => $relatorios,
            'anosDisponiveis' => $this->anosDisponiveis,
        ])->layout('layouts.app');
    }
}
