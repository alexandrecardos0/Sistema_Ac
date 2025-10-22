<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Obra;
use App\Models\Funcionario;
use App\Models\AvariaMaterial;
use App\Models\RegistroHoras;
use Illuminate\Support\Facades\Schema;
use App\Models\RelatorioMensal;
use App\Models\CompraMaterial;
use App\Models\GastoVeiculo;

class Dashboard extends Component
{
    public int $ano;
    public int $mes; // 1..12

    public function mount(): void
    {
        $now = now();
        $this->ano = (int) $now->year;
        $this->mes = (int) $now->month;
    }

    public function updatedAno($value): void
    {
        $value = (int) $value;
        if ($value < 2000) {
            $value = 2000;
        } elseif ($value > 2100) {
            $value = 2100;
        }
        $this->ano = $value;
    }

    public function updatedMes($value): void
    {
        $value = (int) $value;
        if ($value < 1) {
            $value = 1;
        } elseif ($value > 12) {
            $value = 12;
        }
        $this->mes = $value;
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

    public function render()
    {
        $ano = $this->ano;
        $mes = $this->mes;

        $totalFuncionarios = Funcionario::count();
        $funcionariosNoMes = Funcionario::whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->count();

        $statusColumn = $this->obraStatusColumn();
        $obrasEmAndamento = 0;
        if ($statusColumn) {
            $obrasEmAndamento = Obra::whereIn($statusColumn, $this->statusMatches(Obra::STATUS_ANDAMENTO))->count();
        }
        $obrasNoMes = Obra::whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->count();

        $valorRecebidoColumn = $this->valorRecebidoColumn();

        $valorObrasMes = 0.0;
        if ($valorRecebidoColumn) {
            $valorObrasMes = (float) Obra::whereYear('updated_at', $ano)
                ->whereMonth('updated_at', $mes)
                ->sum($valorRecebidoColumn);
        }

        $valorFuncionariosMes = (float) RegistroHoras::whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->sum('total');

        $valorAvariasMes = (float) AvariaMaterial::whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->sum('valor_conserto');

        $valorComprasMes = (float) CompraMaterial::where(function ($query) use ($ano, $mes) {
                $query->whereYear('data_compra', $ano)
                    ->whereMonth('data_compra', $mes);
            })
            ->orWhere(function ($query) use ($ano, $mes) {
                $query->whereNull('data_compra')
                    ->whereYear('created_at', $ano)
                    ->whereMonth('created_at', $mes);
            })
            ->sum('valor_total');

        $valorVeiculosMes = (float) GastoVeiculo::where(function ($query) use ($ano, $mes) {
                $query->whereYear('data_gasto', $ano)
                    ->whereMonth('data_gasto', $mes);
            })
            ->orWhere(function ($query) use ($ano, $mes) {
                $query->whereNull('data_gasto')
                    ->whereYear('created_at', $ano)
                    ->whereMonth('created_at', $mes);
            })
            ->sum('valor');

        $valorSaidasMes = $valorFuncionariosMes + $valorAvariasMes + $valorComprasMes + $valorVeiculosMes;
        $saldoMes = $valorObrasMes - $valorSaidasMes;

        $payload = [
            'ano' => $ano,
            'mes' => $mes,
            'total_funcionarios' => $totalFuncionarios,
            'funcionarios_novos' => $funcionariosNoMes,
            'obras_em_andamento' => $obrasEmAndamento,
            'obras_cadastradas' => $obrasNoMes,
            'valor_obras' => $valorObrasMes,
            'valor_funcionarios' => $valorFuncionariosMes,
            'valor_avarias' => $valorAvariasMes,
            'valor_compras' => $valorComprasMes,
            'valor_veiculos' => $valorVeiculosMes,
            'valor_saidas' => $valorSaidasMes,
            'saldo' => $saldoMes,
            'gerado_em' => now(),
        ];

        RelatorioMensal::updateOrCreate(
            ['ano' => $ano, 'mes' => $mes],
            ['data' => $payload]
        );

        return view('livewire.dashboard', [
            'totalFuncionarios' => $totalFuncionarios,
            'funcionariosNoMes' => $funcionariosNoMes,
            'obrasEmAndamento' => $obrasEmAndamento,
            'obrasNoMes' => $obrasNoMes,
            'valorObrasMes' => $valorObrasMes,
            'valorFuncionariosMes' => $valorFuncionariosMes,
            'valorAvariasMes' => $valorAvariasMes,
            'valorComprasMes' => $valorComprasMes,
            'valorVeiculosMes' => $valorVeiculosMes,
            'valorSaidasMes' => $valorSaidasMes,
            'saldoMes' => $saldoMes,
        ])->layout('layouts.app');
    }

    private function obraStatusColumn(): ?string
    {
        foreach (['status', 'situacao'] as $candidate) {
            if (! Schema::hasColumn('obras', $candidate)) {
                continue;
            }

            try {
                $type = Schema::getColumnType('obras', $candidate);
            } catch (\Throwable $e) {
                continue;
            }

            if (
                in_array($type, ['string', 'text', 'char'], true) ||
                str_starts_with(strtolower($type), 'enum(') ||
                str_starts_with(strtolower($type), 'set(')
            ) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int,string>
     */
    private function statusMatches(string $key): array
    {
        $map = $this->statusOptions();
        $matches = [$key];
        if (isset($map[$key])) {
            $matches[] = $map[$key];
        }

        $map = $this->statusOptions();
        if (isset($map[$key])) {
            $matches[] = $map[$key];
        }

        $normalizedFromMap = array_keys($map);
        foreach ($normalizedFromMap as $normalized) {
            if ($normalized === $key) {
                $matches[] = $map[$normalized];
            }
        }

        return array_unique($matches);
    }

    private function statusOptions(): array
    {
        static $options = null;
        if ($options !== null) {
            return $options;
        }

        $options = Obra::statuses();
        $column = $this->obraStatusColumn();
        if (! $column) {
            return $options;
        }

        try {
            $type = Schema::getColumnType('obras', $column);
        } catch (\Throwable $e) {
            return $options;
        }

        $typeLower = strtolower($type);
        if (str_starts_with($typeLower, 'enum(')) {
            $definition = substr($type, 5, -1);
            $rawValues = explode(',', $definition);
            $values = [];
            foreach ($rawValues as $rawValue) {
                $values[] = trim(str_replace("'", '', $rawValue));
            }

            if (! empty($values)) {
                $options = [];
                foreach ($values as $value) {
                    $options[$this->normalizeStatusKey($value)] = $value;
                }
            }
        }

        return $options;
    }

    private function normalizeStatusKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[\s\-]+/', '_', $value);
        $value = preg_replace('/[^a-z0-9_]/', '', $value);

        return match ($value) {
            'em_andamento', 'andamento' => Obra::STATUS_ANDAMENTO,
            'finalizada', 'concluida' => Obra::STATUS_CONCLUIDA,
            default => $value,
        };
    }

    private function valorRecebidoColumn(): ?string
    {
        foreach (['valor_recebido', 'valor_pago', 'valor_recebido_total', 'valor_pago_total'] as $candidate) {
            if (Schema::hasColumn('obras', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
