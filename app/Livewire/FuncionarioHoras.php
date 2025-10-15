<?php

namespace App\Livewire;

use App\Models\Funcionario;
use App\Models\RegistroHoras;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class FuncionarioHoras extends Component
{
    public Funcionario $funcionario;
    public int $ano;
    public int $mes; // 0..11
    /** @var array<int,string|null> */
    public array $horas = []; // índice = dia (1..31)
    public string $valorHora = '';
    /** @var array<int,string> */
    public array $vales = [
        1 => '',
        2 => '',
    ];

    /** @var array<int,bool> */
    protected array $lockedDiasPrimeira = [];
    /** @var array<int,bool> */
    protected array $lockedDiasSegunda = [];

    /** @var array<int,array{day:int, muted:bool, date:string, weekday:int, enabled:bool}> */
    public array $cells = [];

    public ?RegistroHoras $registroPrimeira = null;
    public ?RegistroHoras $registroSegunda = null;

    public function mount(Funcionario $funcionario): void
    {
        $this->funcionario = $funcionario;
        $now = now();
        $this->ano = (int) $now->year;
        $this->mes = (int) $now->month - 1; // 0..11
        $this->syncPeriodo();
    }

    public function updatedAno($value): void
    {
        $this->ano = max(2000, min(2100, (int) $value));
        $this->syncPeriodo();
    }

    public function updatedMes($value): void
    {
        $this->mes = max(0, min(11, (int) $value));
        $this->syncPeriodo();
    }

    public function getDaysInMonthProperty(): int
    {
        return Carbon::create($this->ano, $this->mes + 1, 1)->daysInMonth;
    }

    public function getTotalHorasPrimeiraProperty(): float
    {
        return $this->calcularTotalHoras(1);
    }

    public function getTotalHorasSegundaProperty(): float
    {
        return $this->calcularTotalHoras(2);
    }

    public function getTotalHorasMesProperty(): float
    {
        return round($this->totalHorasPrimeira + $this->totalHorasSegunda, 2);
    }

    public function getDiasPreenchidosPrimeiraProperty(): int
    {
        return $this->contarDiasComApontamento(1);
    }

    public function getDiasPreenchidosSegundaProperty(): int
    {
        return $this->contarDiasComApontamento(2);
    }

    public function getDiasPreenchidosMesProperty(): int
    {
        return $this->diasPreenchidosPrimeira + $this->diasPreenchidosSegunda;
    }

    public function getValorFinalPrimeiraProperty(): float
    {
        $valorHora = $this->valorHoraParaQuinzena(1);
        $vale = $this->valeParaQuinzena(1);
        $bruto = $this->totalHorasPrimeira * $valorHora;
        return round($bruto - $vale, 2);
    }

    public function getValorFinalSegundaProperty(): float
    {
        $valorHora = $this->valorHoraParaQuinzena(2);
        $vale = $this->valeParaQuinzena(2);
        $bruto = $this->totalHorasSegunda * $valorHora;
        return round($bruto - $vale, 2);
    }

    public function getValorFinalProperty(): float
    {
        return round($this->valorFinalPrimeira + $this->valorFinalSegunda, 2);
    }

    public function pagarPrimeiraQuinzena(): void
    {
        $this->pagarQuinzena(1);
    }

    public function pagarSegundaQuinzena(): void
    {
        $this->pagarQuinzena(2);
    }

    public function render()
    {
        return view('livewire.funcionario-horas')->layout('layouts.app');
    }

    private function rebuildCalendar(): void
    {
        $first = Carbon::create($this->ano, $this->mes + 1, 1);
        // segunda=0 ... domingo=6
        $dow = ($first->dayOfWeekIso - 1);
        $daysInMonth = $first->daysInMonth;

        $prevMonth = $first->copy()->subMonth();
        $prevMonthDays = $prevMonth->daysInMonth;

        $cells = [];
        $lockedPrimeira = $this->lockedDiasPrimeira;
        $lockedSegunda = $this->lockedDiasSegunda;

        // início com mês anterior
        for ($i = 0; $i < $dow; $i++) {
            $dayNum = $prevMonthDays - $dow + 1 + $i;
            $cellDate = $prevMonth->copy()->day($dayNum);
            $cells[] = [
                'day' => $dayNum,
                'muted' => true,
                'date' => $cellDate->toDateString(),
                'weekday' => $cellDate->dayOfWeekIso,
                'enabled' => false,
            ];
        }

        // mês atual
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cellDate = Carbon::create($this->ano, $this->mes + 1, $d);
            $cells[] = [
                'day' => $d,
                'muted' => false,
                'date' => $cellDate->toDateString(),
                'weekday' => $cellDate->dayOfWeekIso,
                'enabled' => ! isset($lockedPrimeira[$d]) && ! isset($lockedSegunda[$d]),
            ];
        }

        // completar grade
        $nextDay = 1;
        while (count($cells) % 7 !== 0) {
            $nextDate = $first->copy()->addMonth()->day($nextDay);
            $cells[] = [
                'day' => $nextDay,
                'muted' => true,
                'date' => $nextDate->toDateString(),
                'weekday' => $nextDate->dayOfWeekIso,
                'enabled' => false,
            ];
            $nextDay++;
        }

        $this->cells = $cells;

        // garantir índices 1..N mantendo valores atuais
        $currentHoras = $this->horas;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $this->horas[$d] = $currentHoras[$d] ?? null;
        }
        foreach ($this->horas as $k => $_) {
            if ($k > $daysInMonth) unset($this->horas[$k]);
        }
    }

    private function normalizeNumber($v): float
    {
        if ($v === null || $v === '') return 0.0;
        $input = trim((string) $v);
        $asDuration = $this->parseHourNotation($input);
        if ($asDuration !== null) {
            return $asDuration;
        }

        $value = str_replace(["\u{00A0}", ' '], '', $input);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    public function getMesesProperty(): array
    {
        return [
            0 => 'Janeiro',
            1 => 'Fevereiro',
            2 => 'Março',
            3 => 'Abril',
            4 => 'Maio',
            5 => 'Junho',
            6 => 'Julho',
            7 => 'Agosto',
            8 => 'Setembro',
            9 => 'Outubro',
            10 => 'Novembro',
            11 => 'Dezembro',
        ];
    }

    public function getQuinzenasProperty(): array
    {
        return [
            1 => '1ª quinzena',
            2 => '2ª quinzena',
        ];
    }

    private function syncPeriodo(): void
    {
        $this->loadRegistros();
        $this->rebuildCalendar();
    }

    private function loadRegistros(): void
    {
        $previousValorHora = $this->valorHora;
        $previousVales = $this->vales + [1 => '', 2 => ''];
        $previousHoras = $this->horas;

        $this->registroPrimeira = $this->funcionario
            ->registroHoras()
            ->where('mes', $this->registroKey(1))
            ->first();

        $this->registroSegunda = $this->funcionario
            ->registroHoras()
            ->where('mes', $this->registroKey(2))
            ->first();

        if (! $this->registroPrimeira && ! $this->registroSegunda) {
            $legacy = $this->funcionario
                ->registroHoras()
                ->where('mes', $this->mesAnoKey())
                ->first();

            if ($legacy) {
                $this->registroPrimeira = $legacy;
            }
        }

        $this->valorHora = $previousValorHora;
        $this->vales = [
            1 => $previousVales[1] ?? '',
            2 => $previousVales[2] ?? '',
        ];

        $this->lockedDiasPrimeira = [];
        $this->lockedDiasSegunda = [];

        $this->horas = [];
        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $this->horas[$d] = $previousHoras[$d] ?? null;
        }

        if ($this->registroPrimeira) {
            $this->valorHora = $this->formatNumber((float) $this->registroPrimeira->valor_hora);
            $this->vales[1] = $this->formatNumber((float) ($this->registroPrimeira->vale ?? 0));
            foreach ((array) $this->registroPrimeira->dias_selecionados as $dia => $valor) {
                $dia = (int) $dia;
                $horas = $this->formatNumber($this->extractHorasValor($valor));
                $this->horas[$dia] = $horas;
                $this->lockedDiasPrimeira[$dia] = true;
            }
        } else {
            $this->vales[1] = $previousVales[1] ?? '';
        }

        if ($this->registroSegunda) {
            if ($this->valorHora === '') {
                $this->valorHora = $this->formatNumber((float) $this->registroSegunda->valor_hora);
            }
            $this->vales[2] = $this->formatNumber((float) ($this->registroSegunda->vale ?? 0));
            foreach ((array) $this->registroSegunda->dias_selecionados as $dia => $valor) {
                $dia = (int) $dia;
                $horas = $this->formatNumber($this->extractHorasValor($valor));
                $this->horas[$dia] = $horas;
                $this->lockedDiasSegunda[$dia] = true;
            }
        } else {
            $this->vales[2] = $previousVales[2] ?? '';
        }
    }

    private function mesAnoKey(): string
    {
        return Carbon::create($this->ano, $this->mes + 1, 1)->format('Y-m');
    }

    private function registroKey(int $quinzena): string
    {
        return $this->mesAnoKey() . '-Q' . $quinzena;
    }

    private function parseHourNotation(string $input): ?float
    {
        $value = mb_strtolower(trim($input));
        $value = str_replace("\u{00A0}", ' ', $value);
        $value = str_replace(['mins', 'min'], '', $value);
        $value = preg_replace('/\s+/', '', $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(?<hours>\d+)h(?<minutes>\d{1,2})?m?$/', $value, $matches)) {
            $hours = (int) $matches['hours'];
            $minutes = isset($matches['minutes']) && $matches['minutes'] !== '' ? (int) $matches['minutes'] : 0;
            return $hours + ($minutes / 60);
        }

        if (preg_match('/^(?<hours>\d+):(?<minutes>\d{1,2})$/', $value, $matches)) {
            $hours = (int) $matches['hours'];
            $minutes = (int) $matches['minutes'];
            return $hours + ($minutes / 60);
        }

        if (preg_match('/^(?<minutes>\d{1,2})m$/', $value, $matches)) {
            $minutes = (int) $matches['minutes'];
            return $minutes / 60;
        }

        return null;
    }

    private function pagarQuinzena(int $quinzena): void
    {
        $registroExistente = $quinzena === 1 ? $this->registroPrimeira : $this->registroSegunda;
        if ($registroExistente) {
            throw ValidationException::withMessages([
                'horas' => 'Esta quinzena já foi registrada. Para refazer, exclua o registro atual.',
            ]);
        }

        if ($quinzena === 2 && ! $this->registroPrimeira) {
            throw ValidationException::withMessages([
                'horas' => 'Registre o pagamento da 1ª quinzena antes de fechar a 2ª.',
            ]);
        }

        $valorHoraDecimal = $this->normalizeNumber($this->valorHora);
        if ($valorHoraDecimal <= 0) {
            throw ValidationException::withMessages([
                'valorHora' => 'Informe um valor de hora válido antes de registrar o pagamento.',
            ]);
        }

        $valeInput = $this->vales[$quinzena] ?? 0;
        $vale = $this->normalizeNumber($valeInput);
        if ($vale < 0) {
            throw ValidationException::withMessages([
                "vales.$quinzena" => 'O vale não pode ser negativo.',
            ]);
        }

        $diasSelecionados = [];
        foreach ($this->diasDisponiveisParaQuinzena($quinzena) as $dia) {
            $valor = $this->normalizeNumber($this->horas[$dia] ?? null);
            if ($valor > 0) {
                $diasSelecionados[$dia] = round($valor, 2);
            }
        }

        if (empty($diasSelecionados)) {
            throw ValidationException::withMessages([
                'horas' => 'Informe horas em pelo menos um dia da quinzena escolhida.',
            ]);
        }

        $totalHoras = array_sum($diasSelecionados);
        $total = round($totalHoras * $valorHoraDecimal - $vale, 2);

        $registro = $this->funcionario
            ->registroHoras()
            ->create([
                'mes' => $this->registroKey($quinzena),
                'ano' => $this->ano,
                'dias_selecionados' => $diasSelecionados,
                'valor_hora' => $valorHoraDecimal,
                'vale' => $vale,
                'total' => $total,
            ]);

        if ($quinzena === 1) {
            $this->registroPrimeira = $registro;
        } else {
            $this->registroSegunda = $registro;
        }

        $this->syncPeriodo();

        session()->flash('message', 'Pagamento da ' . ($this->quinzenas[$quinzena] ?? "quinzena $quinzena") . ' registrado com sucesso.');
    }

    private function calcularTotalHoras(int $quinzena): float
    {
        $registro = $quinzena === 1 ? $this->registroPrimeira : $this->registroSegunda;
        if ($registro) {
            $sum = 0.0;
            foreach ((array) $registro->dias_selecionados as $valor) {
                $sum += $this->extractHorasValor($valor);
            }
            return round($sum, 2);
        }

        $dias = $this->diasDisponiveisParaQuinzena($quinzena);
        if (empty($dias)) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($dias as $dia) {
            $valor = $this->normalizeNumber($this->horas[$dia] ?? null);
            if (is_finite($valor) && $valor > 0) {
                $sum += $valor;
            }
        }

        return round($sum, 2);
    }

    private function contarDiasComApontamento(int $quinzena): int
    {
        $registro = $quinzena === 1 ? $this->registroPrimeira : $this->registroSegunda;
        if ($registro) {
            $count = 0;
            foreach ((array) $registro->dias_selecionados as $valor) {
                if ($this->extractHorasValor($valor) > 0) {
                    $count++;
                }
            }
            return $count;
        }

        $dias = $this->diasDisponiveisParaQuinzena($quinzena);
        if (empty($dias)) {
            return 0;
        }

        $count = 0;
        foreach ($dias as $dia) {
            $valor = $this->normalizeNumber($this->horas[$dia] ?? null);
            if ($valor > 0) {
                $count++;
            }
        }

        return $count;
    }

    private function valorHoraParaQuinzena(int $quinzena): float
    {
        $registro = $quinzena === 1 ? $this->registroPrimeira : $this->registroSegunda;
        if ($registro) {
            return (float) $registro->valor_hora;
        }

        if ($quinzena === 2 && ! $this->registroPrimeira) {
            return 0.0;
        }

        return max(0, $this->normalizeNumber($this->valorHora));
    }

    private function valeParaQuinzena(int $quinzena): float
    {
        $registro = $quinzena === 1 ? $this->registroPrimeira : $this->registroSegunda;
        if ($registro) {
            return (float) ($registro->vale ?? 0);
        }

        if ($quinzena === 2 && ! $this->registroPrimeira) {
            return 0.0;
        }

        return max(0, $this->normalizeNumber($this->vales[$quinzena] ?? 0));
    }

    /**
     * @return array<int,int>
     */
    private function diasDisponiveisParaQuinzena(int $quinzena): array
    {
        $dias = [];
        $totalDias = $this->daysInMonth;

        for ($dia = 1; $dia <= $totalDias; $dia++) {
            if (isset($this->lockedDiasPrimeira[$dia]) || isset($this->lockedDiasSegunda[$dia])) {
                continue;
            }

            if ($quinzena === 2 && ! $this->registroPrimeira) {
                continue;
            }

            $dias[] = $dia;
        }

        return $dias;
    }

    private function extractHorasValor($valor): float
    {
        if (is_array($valor)) {
            $valor = $valor['horas'] ?? ($valor['valor'] ?? ($valor['total'] ?? 0));
        }

        return (float) $valor;
    }
}
