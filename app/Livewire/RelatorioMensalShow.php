<?php

namespace App\Livewire;

use App\Models\RelatorioMensal;
use Livewire\Component;

class RelatorioMensalShow extends Component
{
    public RelatorioMensal $relatorio;

    public function mount(RelatorioMensal $relatorio): void
    {
        $this->relatorio = $relatorio;
    }

    public function render()
    {
        return view('livewire.relatorio-mensal-show', [
            'relatorio' => $this->relatorio,
            'dados' => $this->relatorio->data ?? [],
        ])->layout('layouts.app');
    }
}
