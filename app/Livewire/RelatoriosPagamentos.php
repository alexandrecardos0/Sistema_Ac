<?php

namespace App\Livewire;

use App\Models\Funcionario;
use Illuminate\Support\Carbon;
use Livewire\Component;

class RelatoriosPagamentos extends Component
{
    public int $ano;
    public ?int $mes = null;

    /**
     * @var array<int>
     */
    public array $anosDisponiveis = [];

    public function mount(): void
    {
        $firstPayment = Funcionario::with('registroHoras')
            ->whereHas('registroHoras')
            ->selectRaw('MIN(registro_horas.created_at) as first_payment')
            ->join('registro_horas', 'registro_horas.funcionario_id', '=', 'funcionarios.id')
            ->value('first_payment');

        $startYear = $firstPayment ? Carbon::parse($firstPayment)->year : now()->year;
        $currentYear = now()->year;

        $this->anosDisponiveis = range($currentYear, $startYear);
        $this->ano = $currentYear;
    }

    public function updatedAno($value): void
    {
        $this->ano = (int) $value;
        $this->mes = null;
    }

    public function updatedMes($value): void
    {
        $this->mes = $value ? (int) $value : null;
    }

    public function resetFiltros(): void
    {
        $this->ano = $this->anosDisponiveis[0] ?? now()->year;
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

    public function getFuncionariosProperty()
    {
        $ano = $this->ano;
        $mes = $this->mes;

        return Funcionario::with(['registroHoras' => function ($query) use ($ano, $mes) {
                $query->whereYear('created_at', $ano);
                if ($mes) {
                    $query->whereMonth('created_at', $mes);
                }
            }])
            ->orderBy('nome')
            ->get()
            ->map(function (Funcionario $funcionario) use ($ano, $mes) {
                $pagamentos = $funcionario->registroHoras;
                $totalRecebido = $pagamentos->sum('total');
                $totalHoras = $pagamentos->sum('total') > 0
                    ? round($pagamentos->sum('total') / max($pagamentos->avg('valor_hora'), 1), 2)
                    : 0;

                return [
                    'id' => $funcionario->id,
                    'nome' => $funcionario->nome,
                    'total_recebido' => round($totalRecebido, 2),
                    'total_horas' => $totalHoras,
                    'pagamentos' => $pagamentos->map(function ($registro) {
                        return [
                            'data' => $registro->created_at,
                            'valor' => $registro->total,
                            'horas' => $registro->total,
                        ];
                    }),
                ];
            });
    }

    public function render()
    {
        return view('livewire.relatorios-pagamentos', [
            'funcionarios' => $this->funcionarios,
            'anosDisponiveis' => $this->anosDisponiveis,
        ])->layout('layouts.app');
    }
}
