<?php

namespace App\Livewire;

use Carbon\Carbon;
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

        $valorFuncionariosMes = $this->calcularValorFuncionariosMes($ano, $mes);

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

    private function calcularValorFuncionariosMes(int $ano, int $mes): float
    {
        $inicioMes = Carbon::create($ano, $mes, 1)->startOfDay();
        $fimMes = $inicioMes->copy()->endOfMonth();

        $registros = RegistroHoras::query()
            ->select([
                'id',
                'dias_selecionados',
                'valor_hora',
                'vales_detalhes',
                'vale',
                'mes',
                'ano',
                'periodo_inicio',
                'periodo_fim',
                'created_at',
                'status',
            ])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'draft');
            })
            ->where(function ($query) use ($inicioMes, $fimMes, $ano, $mes) {
                $prefix = sprintf('%04d-%02d', $ano, $mes);
                $query->where(function ($q) use ($inicioMes, $fimMes) {
                    $q->whereNotNull('periodo_inicio')
                        ->whereNotNull('periodo_fim')
                        ->where('periodo_inicio', '<=', $fimMes)
                        ->where('periodo_fim', '>=', $inicioMes);
                })
                    ->orWhere('mes', 'like', $prefix . '%')
                    ->orWhere(function ($q) use ($ano, $mes) {
                        $q->whereNull('periodo_inicio')
                            ->whereNull('periodo_fim')
                            ->whereYear('created_at', $ano)
                            ->whereMonth('created_at', $mes);
                    });
            })
            ->get();

        $total = 0.0;

        foreach ($registros as $registro) {
            $dias = $this->mapRegistroDias($registro);
            $horasPorMesAno = [];

            foreach ($dias as $data => $horas) {
                try {
                    $dataCarbon = Carbon::parse($data);
                } catch (\Throwable $e) {
                    continue;
                }

                $key = $dataCarbon->format('Y-m');
                $horasPorMesAno[$key] = ($horasPorMesAno[$key] ?? 0.0) + $horas;
            }

            $valorHora = (float) ($registro->valor_hora ?? 0);

            foreach ($horasPorMesAno as $key => $horasMes) {
                if ($this->matchesMesAno($key, $ano, $mes)) {
                    $total += $horasMes * $valorHora;
                }
            }

            $valesPorMes = $this->mapValesPorMes($registro, array_keys($horasPorMesAno));

            foreach ($valesPorMes as $key => $valorVale) {
                if ($this->matchesMesAno($key, $ano, $mes)) {
                    $total -= $valorVale;
                }
            }
        }

        return round($total, 2);
    }

    /**
     * @return array<string,float>
     */
    private function mapRegistroDias(RegistroHoras $registro): array
    {
        $dias = (array) ($registro->dias_selecionados ?? []);
        $mapped = [];

        foreach ($dias as $dia => $valor) {
            $data = $this->resolverDataRegistro($registro, $dia);
            if ($data === null) {
                continue;
            }

            $mapped[$data] = round($this->extractHorasValor($valor), 2);
        }

        ksort($mapped);

        return $mapped;
    }

    private function resolverDataRegistro(RegistroHoras $registro, $dia): ?string
    {
        $diaString = (string) $dia;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $diaString)) {
            return $diaString;
        }

        $diaInt = (int) $diaString;
        if ($diaInt < 1) {
            $diaInt = 1;
        }

        $ano = $this->resolverAnoRegistro($registro);
        $mes = $this->resolverMesRegistro($registro);

        try {
            $dataBase = Carbon::create($ano, $mes, 1);
        } catch (\Throwable $e) {
            return null;
        }

        $diasNoMes = $dataBase->daysInMonth;
        if ($diaInt > $diasNoMes) {
            $diaInt = $diasNoMes;
        }

        return $dataBase->copy()->day($diaInt)->format('Y-m-d');
    }

    private function resolverAnoRegistro(RegistroHoras $registro): int
    {
        if ($registro->ano) {
            return (int) $registro->ano;
        }

        if (preg_match('/^(\d{4})-/', (string) $registro->mes, $matches)) {
            return (int) $matches[1];
        }

        if ($registro->periodo_inicio) {
            return (int) $registro->periodo_inicio->year;
        }

        if ($registro->periodo_fim) {
            return (int) $registro->periodo_fim->year;
        }

        return (int) optional($registro->created_at)->year ?? (int) now()->year;
    }

    private function resolverMesRegistro(RegistroHoras $registro): int
    {
        if (preg_match('/^\d{4}-(\d{2})/', (string) $registro->mes, $matches)) {
            return (int) $matches[1];
        }

        if (is_numeric($registro->mes)) {
            $valor = (int) $registro->mes;
            return max(1, min(12, $valor));
        }

        if ($registro->periodo_inicio) {
            return (int) $registro->periodo_inicio->month;
        }

        if ($registro->periodo_fim) {
            return (int) $registro->periodo_fim->month;
        }

        return (int) now()->month;
    }

    private function extractHorasValor($valor): float
    {
        if (is_array($valor)) {
            $valor = $valor['horas'] ?? ($valor['valor'] ?? ($valor['total'] ?? 0));
        }

        return (float) $valor;
    }

    /**
     * @param array<int,string> $horasKeys
     * @return array<string,float>
     */
    private function mapValesPorMes(RegistroHoras $registro, array $horasKeys): array
    {
        $resultado = [];
        $detalhes = $registro->vales_detalhes;

        if (is_array($detalhes) && ! empty($detalhes)) {
            foreach ($detalhes as $vale) {
                if (! is_array($vale)) {
                    continue;
                }

                $valor = isset($vale['valor']) ? (float) $vale['valor'] : 0.0;
                if ($valor <= 0) {
                    continue;
                }

                $mesVale = isset($vale['mes']) ? (int) $vale['mes'] : null;
                $key = $this->matchAnoMesForVale($mesVale, $horasKeys, $registro);
                $resultado[$key] = ($resultado[$key] ?? 0.0) + round($valor, 2);
            }

            return $resultado;
        }

        $valorTotal = (float) ($registro->vale ?? 0.0);
        if ($valorTotal <= 0) {
            return $resultado;
        }

        $key = $this->matchAnoMesForVale(null, $horasKeys, $registro);
        $resultado[$key] = ($resultado[$key] ?? 0.0) + round($valorTotal, 2);

        return $resultado;
    }

    /**
     * @param array<int,string> $horasKeys
     */
    private function matchAnoMesForVale(?int $mesVale, array $horasKeys, RegistroHoras $registro): string
    {
        if ($mesVale !== null) {
            foreach ($horasKeys as $key) {
                if (! is_string($key)) {
                    continue;
                }

                $month = (int) substr($key, 5, 2);
                if ($month === $mesVale) {
                    return $key;
                }
            }
        }

        if (! empty($horasKeys)) {
            $first = reset($horasKeys);
            if (is_string($first)) {
                return $first;
            }
        }

        $ano = $this->resolverAnoRegistro($registro);
        $mes = $mesVale ?? $this->resolverMesRegistro($registro);
        $mes = max(1, min(12, $mes));

        return sprintf('%04d-%02d', $ano, $mes);
    }

    private function matchesMesAno(string $key, int $ano, int $mes): bool
    {
        return $key === sprintf('%04d-%02d', $ano, $mes);
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
