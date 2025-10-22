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

    /** @var array<string,string|null> */
    public array $horas = [];

    public string $valorHora = '';

    /** @var array<int,array{descricao:string,valor:float,dia:int|null}> */
    public array $vales = [];
    public string $novoValeDescricao = '';
    public string $novoValeValor = '';
    public string $novoValeDia = '';
    public string $novoValeMes = '';

    /** @var array<int,array<string,mixed>> */
    public array $pagamentos = [];

    /** @var array<int,array<string,mixed>> */
    public array $cells = [];

    /** @var array<string,bool> */
    protected array $lockedDatas = [];

    protected ?RegistroHoras $rascunho = null;

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

    public function navigateToPreviousMonth(): void
    {
        $this->mes--;
        if ($this->mes < 0) {
            $this->mes = 11;
            $this->ano--;
        }

        $this->syncPeriodo();
    }

    public function navigateToNextMonth(): void
    {
        $this->mes++;
        if ($this->mes > 11) {
            $this->mes = 0;
            $this->ano++;
        }

        $this->syncPeriodo();
    }

    public function addVale(): void
    {
        $descricao = trim((string) $this->novoValeDescricao);
        $valor = $this->normalizeNumber($this->novoValeValor);
        $dia = (int) $this->normalizeNumber($this->novoValeDia);
        $mesInput = $this->novoValeMes !== '' ? $this->normalizeNumber($this->novoValeMes) : ($this->mes + 1);
        $mes = (int) $mesInput;

        if ($valor <= 0) {
            throw ValidationException::withMessages([
                'novoValeValor' => 'Informe um valor positivo para o vale.',
            ]);
        }

        if (mb_strlen($descricao) > 180) {
            throw ValidationException::withMessages([
                'novoValeDescricao' => 'A descrição pode ter no máximo 180 caracteres.',
            ]);
        }

        $maxDia = Carbon::create($this->ano, $this->mes + 1, 1)->daysInMonth;
        if ($dia < 1 || $dia > $maxDia) {
            throw ValidationException::withMessages([
                'novoValeDia' => "Informe um dia entre 1 e $maxDia.",
            ]);
        }

        if ($mes < 1 || $mes > 12) {
            throw ValidationException::withMessages([
                'novoValeMes' => 'Informe um mês entre 1 e 12.',
            ]);
        }

        $this->vales[] = [
            'descricao' => $descricao,
            'valor' => round($valor, 2),
            'dia' => $dia,
            'mes' => $mes,
        ];

        $this->novoValeDescricao = '';
        $this->novoValeValor = '';
        $this->novoValeDia = '';
        $this->novoValeMes = (string) max(1, min(12, $mes));
        $this->resetErrorBag(['novoValeDescricao', 'novoValeValor', 'novoValeDia', 'novoValeMes']);
    }

    public function removeVale(int $index): void
    {
        if (! isset($this->vales[$index])) {
            return;
        }

        unset($this->vales[$index]);
        $this->vales = array_values($this->vales);
    }

    public function saveHoras(): void
    {
        $horasParaSalvar = $this->horasNaoQuitadas();
        $valesParaSalvar = $this->prepareValesForStorage();
        $totalVales = array_sum(array_column($valesParaSalvar, 'valor'));

        $mesKey = $this->mesAnoKey();

        if (empty($horasParaSalvar) && $totalVales <= 0) {
            $this->funcionario
                ->registroHoras()
                ->where('mes', $mesKey)
                ->where('status', 'draft')
                ->delete();

            $this->rascunho = null;
            $this->vales = [];
            $this->novoValeDescricao = '';
            $this->novoValeValor = '';
            $this->novoValeDia = '';
            $this->novoValeMes = (string) ($this->mes + 1);
            $this->syncPeriodo();

            session()->flash('message', 'Horas em aberto removidas.');
            return;
        }

        $valorHoraDecimal = max(0, $this->normalizeNumber($this->valorHora));

        $registro = $this->funcionario
            ->registroHoras()
            ->updateOrCreate(
                [
                    'mes' => $mesKey,
                    'status' => 'draft',
                ],
                [
                    'ano' => $this->ano,
                    'dias_selecionados' => $horasParaSalvar,
                    'valor_hora' => $valorHoraDecimal,
                    'vale' => $totalVales,
                    'vales_detalhes' => $valesParaSalvar,
                    'total' => 0,
                    'status' => 'draft',
                ]
            );

        $this->rascunho = $registro;
        $this->syncPeriodo();

        session()->flash('message', 'Horas salvas com sucesso.');
    }

    public function adicionarPagamento(): void
    {
        $diasSelecionados = $this->horasNaoQuitadas();
        if (empty($diasSelecionados)) {
            throw ValidationException::withMessages([
                'horas' => 'Informe horas em pelo menos um dia para registrar o pagamento.',
            ]);
        }

        $valorHoraDecimal = $this->normalizeNumber($this->valorHora);
        if ($valorHoraDecimal <= 0) {
            throw ValidationException::withMessages([
                'valorHora' => 'Informe um valor de hora válido antes de registrar o pagamento.',
            ]);
        }

        $valesDetalhes = $this->prepareValesForStorage();
        $totalVales = array_sum(array_column($valesDetalhes, 'valor'));

        $datas = array_keys($diasSelecionados);
        sort($datas);

        $periodoInicio = Carbon::parse(reset($datas));
        $periodoFim = Carbon::parse(end($datas));

        $totalHoras = array_sum($diasSelecionados);
        $total = round($totalHoras * $valorHoraDecimal - $totalVales, 2);

        $mesKey = $this->mesAnoKey();
        $sequencia = $this->determinarProximaSequencia($mesKey);

        $this->funcionario
            ->registroHoras()
            ->create([
                'mes' => sprintf('%s-P%02d', $mesKey, $sequencia),
                'ano' => $this->ano,
                'dias_selecionados' => $diasSelecionados,
                'valor_hora' => $valorHoraDecimal,
                'vale' => $totalVales,
                'vales_detalhes' => $valesDetalhes,
                'total' => $total,
                'status' => 'closed',
                'periodo_inicio' => $periodoInicio,
                'periodo_fim' => $periodoFim,
            ]);

        $restantes = $this->horasRestantesApos($diasSelecionados);

        if (empty($restantes)) {
            $this->funcionario
                ->registroHoras()
                ->where('mes', $mesKey)
                ->where('status', 'draft')
                ->delete();
            $this->rascunho = null;
        } else {
            $this->funcionario
                ->registroHoras()
                ->updateOrCreate(
                    [
                        'mes' => $mesKey,
                        'status' => 'draft',
                    ],
                    [
                        'ano' => $this->ano,
                        'dias_selecionados' => $restantes,
                        'valor_hora' => max(0, $this->normalizeNumber($this->valorHora)),
                        'vale' => 0,
                        'vales_detalhes' => [],
                        'total' => 0,
                        'status' => 'draft',
                    ]
                );
        }

        $this->vales = [];
        $this->novoValeDescricao = '';
        $this->novoValeValor = '';
        $this->novoValeDia = '';
        $this->novoValeMes = (string) ($this->mes + 1);
        $this->syncPeriodo();

        session()->flash('message', 'Pagamento registrado com sucesso.');
    }

    public function getDaysInMonthProperty(): int
    {
        return Carbon::create($this->ano, $this->mes + 1, 1)->daysInMonth;
    }

    public function getTotalHorasAbertasProperty(): float
    {
        $sum = 0.0;
        foreach ($this->horas as $data => $valor) {
            if (isset($this->lockedDatas[$data])) {
                continue;
            }

            $horas = $this->normalizeNumber($valor);
            if ($horas > 0) {
                $sum += $horas;
            }
        }

        return round($sum, 2);
    }

    public function getDiasPreenchidosAbertosProperty(): int
    {
        $count = 0;
        foreach ($this->horas as $data => $valor) {
            if (isset($this->lockedDatas[$data])) {
                continue;
            }

            if ($this->normalizeNumber($valor) > 0) {
                $count++;
            }
        }

        return $count;
    }

    public function getTotalHorasPagasProperty(): float
    {
        $sum = 0.0;
        foreach ($this->pagamentos as $pagamento) {
            $sum += (float) ($pagamento['total_horas'] ?? 0);
        }

        return round($sum, 2);
    }

    public function getTotalDiasPagosProperty(): int
    {
        $sum = 0;
        foreach ($this->pagamentos as $pagamento) {
            $sum += (int) ($pagamento['quantidade_dias'] ?? 0);
        }

        return $sum;
    }

    public function getValorTotalPagamentosProperty(): float
    {
        $sum = 0.0;
        foreach ($this->pagamentos as $pagamento) {
            $sum += (float) ($pagamento['total'] ?? 0);
        }

        return round($sum, 2);
    }

    public function getTotalValesProperty(): float
    {
        $sum = 0.0;
        foreach ($this->prepareValesForStorage() as $vale) {
            $sum += $vale['valor'];
        }

        return round($sum, 2);
    }

    public function getValorHoraDecimalProperty(): float
    {
        return max(0, $this->normalizeNumber($this->valorHora));
    }

    public function getValorEstimadoAbertoProperty(): float
    {
        $bruto = $this->totalHorasAbertas * $this->valorHoraDecimal;
        return round($bruto - $this->totalVales, 2);
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

    public function render()
    {
        return view('livewire.funcionario-horas')->layout('layouts.app');
    }

    private function syncPeriodo(): void
    {
        $this->loadRegistros();
        $this->rebuildCalendar();
    }

    private function loadRegistros(): void
    {
        $previousValorHora = $this->valorHora;
        $previousVales = $this->vales;
        $previousHoras = $this->horas;

        $this->horas = [];
        $this->lockedDatas = [];
        $this->pagamentos = [];
        $this->rascunho = null;
        $this->vales = [];
        $this->novoValeDescricao = '';
        $this->novoValeValor = '';
        $this->novoValeDia = '';
        $this->novoValeMes = (string) ($this->mes + 1);

        $mesKey = $this->mesAnoKey();

        $registros = $this->funcionario
            ->registroHoras()
            ->where('mes', 'like', $mesKey . '%')
            ->orderBy('created_at')
            ->get();

        $ultimoValorHora = null;
        $indice = 1;

        foreach ($registros as $registro) {
            $status = $registro->status ?? 'closed';
            $dias = $this->mapRegistroDias($registro);

            if ($status === 'draft') {
                $this->rascunho = $registro;
                $this->vales = $this->normalizeVales($registro->vales_detalhes ?? [], (float) ($registro->vale ?? 0));
                foreach ($dias as $data => $valor) {
                    $this->horas[$data] = $this->formatNumber($valor);
                }
                if ($registro->valor_hora) {
                    $ultimoValorHora = (float) $registro->valor_hora;
                }
                continue;
            }

            if ($registro->valor_hora) {
                $ultimoValorHora = (float) $registro->valor_hora;
            }

            $totalHoras = 0.0;
            foreach ($dias as $data => $valor) {
                $totalHoras += $valor;
                $this->horas[$data] = $this->formatNumber($valor);
                $this->lockedDatas[$data] = true;
            }

            $datasPagamento = array_keys($dias);
            sort($datasPagamento);

            $valesDetalhes = $this->normalizeVales($registro->vales_detalhes ?? [], (float) ($registro->vale ?? 0));

            $this->pagamentos[] = [
                'id' => $registro->id,
                'label' => $this->buildPagamentoLabel($registro, $indice),
                'periodo_inicio' => $registro->periodo_inicio?->toDateString() ?? ($datasPagamento[0] ?? null),
                'periodo_fim' => $registro->periodo_fim?->toDateString() ?? (end($datasPagamento) ?: null),
                'total_horas' => round($totalHoras, 2),
                'valor_hora' => (float) $registro->valor_hora,
                'vale' => round(array_sum(array_column($valesDetalhes, 'valor')), 2),
                'vales_detalhes' => $valesDetalhes,
                'total' => (float) $registro->total,
                'quantidade_dias' => count($dias),
            ];

            $indice++;
        }

        foreach ($previousHoras as $data => $valor) {
            if (isset($this->lockedDatas[$data])) {
                continue;
            }
            if (! isset($this->horas[$data]) && $valor !== null && $valor !== '') {
                $this->horas[$data] = $valor;
            }
        }

        if ($previousValorHora !== '') {
            $this->valorHora = $previousValorHora;
        } elseif ($ultimoValorHora !== null) {
            $this->valorHora = $this->formatNumber($ultimoValorHora);
        } else {
            $this->valorHora = '';
        }

        if (empty($this->vales) && ! empty($previousVales)) {
            $this->vales = $this->normalizeVales($previousVales, 0.0);
        }
    }

    private function rebuildCalendar(): void
    {
        $primeiroDiaMes = Carbon::create($this->ano, $this->mes + 1, 1);
        $inicioGrade = $primeiroDiaMes->copy()->subDays($primeiroDiaMes->dayOfWeekIso - 1);

        $cells = [];
        for ($i = 0; $i < 42; $i++) {
            $date = $inicioGrade->copy()->addDays($i);
            $dataString = $date->toDateString();
            $locked = isset($this->lockedDatas[$dataString]);

            $cells[] = [
                'day' => $date->day,
                'muted' => $date->month !== $primeiroDiaMes->month,
                'date' => $dataString,
                'weekday' => $date->dayOfWeekIso,
                'enabled' => ! $locked,
                'locked' => $locked,
            ];
        }

        $this->cells = $cells;
    }

    private function horasNaoQuitadas(): array
    {
        $resultado = [];
        foreach ($this->horas as $data => $valor) {
            if (! is_string($data) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                continue;
            }
            if (isset($this->lockedDatas[$data])) {
                continue;
            }
            $numero = $this->normalizeNumber($valor);
            if ($numero > 0) {
                $resultado[$data] = round($numero, 2);
            }
        }

        ksort($resultado);

        return $resultado;
    }

    /**
     * @param array<string,float> $fechadas
     * @return array<string,float>
     */
    private function horasRestantesApos(array $fechadas): array
    {
        $restantes = [];

        foreach ($this->horas as $data => $valor) {
            if (! is_string($data) || isset($fechadas[$data]) || isset($this->lockedDatas[$data])) {
                continue;
            }

            $numero = $this->normalizeNumber($valor);
            if ($numero > 0) {
                $restantes[$data] = round($numero, 2);
            }
        }

        ksort($restantes);

        return $restantes;
    }

    private function mesAnoKey(): string
    {
        return Carbon::create($this->ano, $this->mes + 1, 1)->format('Y-m');
    }

    private function determinarProximaSequencia(string $mesKey): int
    {
        $registros = $this->funcionario
            ->registroHoras()
            ->where('mes', 'like', $mesKey . '%')
            ->pluck('mes');

        $maior = 0;
        foreach ($registros as $mes) {
            if (preg_match('/-P(\d+)/', (string) $mes, $matches)) {
                $maior = max($maior, (int) $matches[1]);
            } elseif (preg_match('/-Q([12])/', (string) $mes, $matches)) {
                $maior = max($maior, (int) $matches[1]);
            }
        }

        return $maior + 1;
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
            $mapped[$data] = round($this->extractHorasValor($valor), 2);
        }

        ksort($mapped);

        return $mapped;
    }

    private function resolverDataRegistro(RegistroHoras $registro, $dia): string
    {
        $dia = (string) $dia;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia)) {
            return $dia;
        }

        $diaInt = (int) $dia;
        if ($diaInt < 1) {
            $diaInt = 1;
        }

        $ano = $this->resolverAnoRegistro($registro);
        $mes = $this->resolverMesRegistro($registro);

        $dataBase = Carbon::create($ano, $mes, 1);
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

        return $this->ano;
    }

    private function resolverMesRegistro(RegistroHoras $registro): int
    {
        if (preg_match('/^\d{4}-(\d{2})/', (string) $registro->mes, $matches)) {
            return (int) $matches[1];
        }

        return $this->mes + 1;
    }

    private function buildPagamentoLabel(RegistroHoras $registro, int $indice): string
    {
        $mes = (string) $registro->mes;
        if (preg_match('/-Q([12])$/', $mes, $matches)) {
            return $matches[1] === '1' ? '1ª quinzena' : '2ª quinzena';
        }

        if (preg_match('/-P(\d+)/', $mes, $matches)) {
            return 'Pagamento ' . (int) $matches[1];
        }

        return 'Pagamento ' . $indice;
    }

    /**
     * @return array<int,array{descricao:string,valor:float,dia:int|null,mes:int|null}>
     */
    private function prepareValesForStorage(): array
    {
        $resultado = [];
        foreach ($this->vales as $vale) {
            $valorBruto = $vale['valor'] ?? null;
            $valor = $this->normalizeNumber($valorBruto);
            if ($valor <= 0) {
                continue;
            }

            $descricao = trim((string) ($vale['descricao'] ?? ''));
            $dia = isset($vale['dia']) ? (int) $vale['dia'] : null;
            $mes = isset($vale['mes']) ? (int) $vale['mes'] : null;

            if (($dia === null || $mes === null) && isset($vale['data'])) {
                try {
                    $parsed = Carbon::parse($vale['data']);
                    $dia = $dia ?? $parsed->day;
                    $mes = $mes ?? $parsed->month;
                } catch (\Throwable $e) {
                    // ignore parse failures
                }
            }

            if ($dia !== null) {
                $dia = max(1, min(31, $dia));
            }

            if ($mes === null) {
                $mes = $this->mes + 1;
            }
            $mes = max(1, min(12, $mes));

            $resultado[] = [
                'descricao' => $descricao,
                'valor' => round($valor, 2),
                'dia' => $dia,
                'mes' => $mes,
            ];
        }

        return $resultado;
    }

    /**
     * @param mixed $vales
     * @return array<int,array{descricao:string,valor:float,dia:int|null,mes:int|null}>
     */
    private function normalizeVales($vales, float $fallbackTotal): array
    {
        $resultado = [];

        if (is_array($vales)) {
            foreach ($vales as $vale) {
                if (is_array($vale)) {
                    $valor = $this->normalizeNumber($vale['valor'] ?? 0);
                    if ($valor <= 0) {
                        continue;
                    }
                    $descricao = trim((string) ($vale['descricao'] ?? ''));
                    $dia = $vale['dia'] ?? null;
                    $mes = $vale['mes'] ?? null;

                    if (($dia === null || $mes === null) && isset($vale['data'])) {
                        try {
                            $parsed = Carbon::parse($vale['data']);
                            $dia = $dia ?? $parsed->day;
                            $mes = $mes ?? $parsed->month;
                        } catch (\Throwable $e) {
                            // ignore parse failures
                        }
                    }

                    if ($dia !== null) {
                        $dia = max(1, min(31, (int) $dia));
                    }
                    if ($mes === null) {
                        $mes = $this->mes + 1;
                    }
                    $mes = max(1, min(12, (int) $mes));

                    $resultado[] = [
                        'descricao' => $descricao,
                        'valor' => round($valor, 2),
                        'dia' => $dia,
                        'mes' => $mes,
                    ];
                    continue;
                }

                if (is_numeric($vale)) {
                    $valor = (float) $vale;
                    if ($valor <= 0) {
                        continue;
                    }
                    $resultado[] = [
                        'descricao' => '',
                        'valor' => round($valor, 2),
                        'dia' => null,
                        'mes' => $this->mes + 1,
                    ];
                }
            }
        }

        if (empty($resultado) && $fallbackTotal > 0) {
            $resultado[] = [
                'descricao' => '',
                'valor' => round($fallbackTotal, 2),
                'dia' => null,
                'mes' => $this->mes + 1,
            ];
        }

        return $resultado;
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

    private function extractHorasValor($valor): float
    {
        if (is_array($valor)) {
            $valor = $valor['horas'] ?? ($valor['valor'] ?? ($valor['total'] ?? 0));
        }

        return (float) $valor;
    }
}
