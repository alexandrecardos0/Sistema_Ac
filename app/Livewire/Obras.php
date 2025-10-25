<?php

namespace App\Livewire;

use App\Models\Obra;
use App\Models\ObraRecebimento;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Obras extends Component
{
    public Collection $obras;

    public bool $showCreateModal = false;
    public bool $showEditModal = false;

    public string $nome = '';
    public string $status = Obra::STATUS_ANDAMENTO;
    public string $horasTrabalhadas = '';
    public string $descricao = '';
    public string $endereco = '';

    public ?Obra $obraBeingEdited = null;
    public string $statusEdit = '';
    public string $novoRecebimentoValor = '';
    public string $novoRecebimentoData = '';
    /** @var array<int,array{id:int,valor:float,data:string|null}> */
    public array $recebimentos = [];

    /** @var array<string,array{name:string,type:string,null:bool,default:mixed}> */
    private array $columnsMeta = [];

    public function mount(): void
    {
        $this->obras = collect();
        $this->status = array_key_first($this->statuses) ?? Obra::STATUS_ANDAMENTO;
        $this->loadObras();
    }

    public function render()
    {
        return view('livewire.obras')->layout('layouts.app');
    }

    public function getSupportsStatusProperty(): bool
    {
        return $this->statusColumnAllowsString() && !empty($this->statuses);
    }

    public function getTotalObrasProperty(): int
    {
        return $this->obras->count();
    }

    public function getObrasConcluidasProperty(): int
    {
        if (! $this->statusColumnAllowsString()) {
            return 0;
        }

        return $this->obras->where('status_key', Obra::STATUS_CONCLUIDA)->count();
    }

    public function getObrasEmAndamentoProperty(): int
    {
        if (! $this->statusColumnAllowsString()) {
            return 0;
        }

        return $this->obras->where('status_key', Obra::STATUS_ANDAMENTO)->count();
    }

    public function getTotalReceberProperty(): float
    {
        return (float) $this->obras->sum('valor_pendente_display');
    }

    public function getTotalRecebidoProperty(): float
    {
        return (float) $this->obras->sum('valor_recebido_display');
    }

    public function getValorTotalProperty(): float
    {
        return (float) $this->obras->sum('valor_total_display');
    }

    public function getStatusesProperty(): array
    {
        if (! $this->statusColumnAllowsString()) {
            return [];
        }

        $column = $this->statusColumn();
        if (! $column) {
            return $this->normalizedStatusArray(Obra::statuses());
        }

        $meta = $this->tableMeta($column);
        if (! $meta) {
            return $this->normalizedStatusArray(Obra::statuses());
        }

        $typeLower = strtolower($meta['type']);
        $values = [];
        if (str_starts_with($typeLower, 'enum(')) {
            $definition = substr($meta['type'], 5, -1);
            $rawValues = explode(',', $definition);
            foreach ($rawValues as $rawValue) {
                $values[] = trim(str_replace("'", '', $rawValue));
            }
        }

        if (empty($values)) {
            return $this->normalizedStatusArray(Obra::statuses());
        }

        $options = [];
        foreach ($values as $value) {
            $options[$this->normalizeStatusKey($value)] = $value;
        }

        return $options;
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createObra(): void
    {
        $statusOptions = $this->statuses;
        $statusKeys = array_keys($statusOptions);
        $statusRule = $this->statusColumnAllowsString() && !empty($statusKeys)
            ? ['required', 'in:' . implode(',', $statusKeys)]
            : ['nullable'];

        $validated = $this->validate([
            'nome' => ['required', 'string', 'max:255'],
            'status' => $statusRule,
            'horasTrabalhadas' => ['nullable', 'string'],
            'descricao' => ['nullable', 'string'],
            'endereco' => ['nullable', 'string', 'max:255'],
        ], [], [
            'nome' => 'nome da obra',
            'status' => 'status',
            'horasTrabalhadas' => 'horas trabalhadas',
            'descricao' => 'descrição',
            'endereco' => 'endereço',
        ]);

        $horas = $this->normalizeNumber($validated['horasTrabalhadas'] ?? '');

        $horas = max($horas, 0);
        $valor = 0.0;

        $payload = [
            'nome' => $validated['nome'],
        ];

        if ($enderecoColumn = $this->enderecoColumn()) {
            $payload[$enderecoColumn] = $validated['endereco'] ?: 'Endereço não informado';
        }

        if ($this->statusColumnAllowsString() && ($statusColumn = $this->statusColumn()) && !empty($statusOptions)) {
            $payload[$statusColumn] = $statusOptions[$validated['status']] ?? $validated['status'];
        }

        if ($descricaoColumn = $this->descricaoColumn()) {
            $payload[$descricaoColumn] = $validated['descricao'] ?: null;
        }

        if ($valorColumn = $this->valorColumn()) {
            $payload[$valorColumn] = $valor;
            if ($valorColumn !== 'valor' && Schema::hasColumn('obras', 'valor')) {
                $payload['valor'] = $valor;
            }
        } elseif (Schema::hasColumn('obras', 'valor')) {
            $payload['valor'] = $valor;
        }

        if ($valorTotalColumn = $this->valorTotalColumn()) {
            $payload[$valorTotalColumn] = $valor;
        }

        if ($valorRecebidoColumn = $this->valorRecebidoColumn()) {
            $payload[$valorRecebidoColumn] = 0;
        }

        if ($horasColumn = $this->horasColumn()) {
            $payload[$horasColumn] = $horas;
        }

        foreach ($this->requiredAdditionalColumns() as $column) {
            if (! array_key_exists($column['name'], $payload)) {
                $payload[$column['name']] = $column['default'] ?? $this->defaultForType($column['type']);
            }
        }

        Obra::create($payload);

        session()->flash('message', 'Obra cadastrada com sucesso.');

        $this->closeCreateModal();
        $this->loadObras();
    }

    public function openEditModal(int $obraId): void
    {
        $obra = Obra::find($obraId);
        if (! $obra) {
            session()->flash('message', 'Obra não encontrada.');
            return;
        }

        $valorRecebidoColumn = $this->valorRecebidoColumn();
        if (! $valorRecebidoColumn) {
            session()->flash('message', 'Não há coluna configurada para registrar valores recebidos nesta base de dados.');
            return;
        }

        $valorTotalColumn = $this->valorTotalColumn();
        $valorColumn = $this->valorColumn();

        $valorTotal = $valorTotalColumn ? (float) ($obra->{$valorTotalColumn} ?? 0) : 0.0;
        $valorPrincipal = $valorColumn ? (float) ($obra->{$valorColumn} ?? $valorTotal) : $valorTotal;
        if ($valorTotal <= 0 && $valorPrincipal > 0) {
            $valorTotal = $valorPrincipal;
        }

        $valorRecebidoAtual = (float) ($obra->{$valorRecebidoColumn} ?? 0);
        $valorPendente = max($valorTotal - $valorRecebidoAtual, 0);

        $obra->valor_total_display = round($valorTotal, 2);
        $obra->valor_recebido_display = round($valorRecebidoAtual, 2);
        $obra->valor_pendente_display = round($valorPendente, 2);

        $this->obraBeingEdited = $obra;
        $this->novoRecebimentoValor = '';
        $this->novoRecebimentoData = now()->toDateString();
        $this->carregarRecebimentos($obra);
        $statusOptions = $this->statuses;
        if ($this->statusColumnAllowsString() && ($statusColumn = $this->statusColumn()) && !empty($statusOptions)) {
            $current = $obra->{$statusColumn} ?? null;
            $normalized = $current ? $this->normalizeStatusKey((string) $current) : null;
            $this->statusEdit = $normalized && array_key_exists($normalized, $statusOptions)
                ? $normalized
                : array_key_first($statusOptions);
        } else {
            $this->statusEdit = array_key_first($statusOptions) ?? Obra::STATUS_ANDAMENTO;
        }

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->obraBeingEdited = null;
        $this->statusEdit = '';
        $this->novoRecebimentoValor = '';
        $this->novoRecebimentoData = '';
        $this->recebimentos = [];
    }

    public function updateValores(): void
    {
        if (! $this->obraBeingEdited) {
            return;
        }

        $payload = [];

        if ($this->statusColumnAllowsString() && ($statusColumn = $this->statusColumn())) {
            $options = $this->statuses;
            if (! empty($options)) {
                $payload[$statusColumn] = $options[$this->statusEdit] ?? $this->statusEdit;
            }
        }

        if (! empty($payload)) {
            $this->obraBeingEdited->update($payload);
            session()->flash('message', 'Dados da obra atualizados com sucesso.');
        }

        $this->closeEditModal();
        $this->loadObras();
    }

    public function adicionarRecebimento(): void
    {
        if (! $this->obraBeingEdited) {
            return;
        }

        $valor = $this->normalizeNumber($this->novoRecebimentoValor);
        if ($valor <= 0) {
            throw ValidationException::withMessages([
                'novoRecebimentoValor' => 'Informe um valor positivo para registrar o recebimento.',
            ]);
        }

        $dataInput = $this->novoRecebimentoData !== ''
            ? $this->novoRecebimentoData
            : now()->toDateString();

        try {
            $data = Carbon::parse($dataInput)->toDateString();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'novoRecebimentoData' => 'Informe uma data válida para o recebimento.',
            ]);
        }

        ObraRecebimento::create([
            'obra_id' => $this->obraBeingEdited->id,
            'valor' => round($valor, 2),
            'data' => $data,
        ]);

        $this->obraBeingEdited->refresh();
        $this->sincronizarValorRecebido($this->obraBeingEdited);

        $this->loadObras();
        $this->openEditModal($this->obraBeingEdited->id);

        $this->novoRecebimentoValor = '';
        $this->novoRecebimentoData = $data;

        session()->flash('message', 'Recebimento lançado com sucesso.');
    }

    public function deleteObra(int $obraId): void
    {
        $obra = Obra::find($obraId);
        if ($obra) {
            $obra->delete();
            session()->flash('message', 'Obra excluída com sucesso.');
            $this->loadObras();
        }
    }

    private function carregarRecebimentos(Obra $obra): void
    {
        $this->recebimentos = $obra->recebimentos()
            ->orderByDesc('data')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (ObraRecebimento $recebimento) {
                return [
                    'id' => $recebimento->id,
                    'valor' => round((float) $recebimento->valor, 2),
                    'data' => $recebimento->data ? $recebimento->data->format('d/m/Y') : null,
                ];
            })
            ->toArray();
    }

    private function loadObras(): void
    {
        $valorColumn = $this->valorColumn();
        $valorTotalColumn = $this->valorTotalColumn();
        $valorRecebidoColumn = $this->valorRecebidoColumn();
        $horasColumn = $this->horasColumn();
        $horasRecebidasColumn = $this->horasRecebidasColumn();
        $statusColumn = $this->statusColumn();
        $descricaoColumn = $this->descricaoColumn();
        $statusAllowsString = $this->statusColumnAllowsString();

        $this->obras = Obra::orderByDesc('created_at')->get()->map(function (Obra $obra) use ($valorColumn, $valorTotalColumn, $valorRecebidoColumn, $horasColumn, $horasRecebidasColumn, $statusColumn, $descricaoColumn, $statusAllowsString) {
            $valorTotal = $valorTotalColumn ? (float) ($obra->{$valorTotalColumn} ?? 0) : 0.0;
            $valorPrincipal = $valorColumn ? (float) ($obra->{$valorColumn} ?? $valorTotal) : $valorTotal;
            if ($valorTotal <= 0 && $valorPrincipal > 0) {
                $valorTotal = $valorPrincipal;
            }

            $valorRecebido = $valorRecebidoColumn ? (float) ($obra->{$valorRecebidoColumn} ?? 0) : 0.0;
            $valorPendente = max($valorTotal - $valorRecebido, 0);

            $obra->valor_total_display = round($valorTotal, 2);
            $obra->valor_recebido_display = round($valorRecebido, 2);
            $obra->valor_pendente_display = round($valorPendente, 2);
            $obra->valor_display = $obra->valor_pendente_display;

            $obra->horas_display = $horasColumn ? (float) ($obra->{$horasColumn} ?? 0) : 0.0;
            $obra->horas_recebidas_display = $horasRecebidasColumn ? (float) ($obra->{$horasRecebidasColumn} ?? 0) : 0.0;

            $statusValue = $statusColumn ? ($obra->{$statusColumn} ?? null) : ($obra->status ?? null);
            $normalizedStatus = null;
            if ($statusAllowsString && $statusValue !== null) {
                $candidate = $this->normalizeStatusKey((string) $statusValue);
                if (array_key_exists($candidate, $this->statuses)) {
                    $normalizedStatus = $candidate;
                } else {
                    foreach ($this->statuses as $key => $label) {
                        if (strcasecmp((string) $label, (string) $statusValue) === 0) {
                            $normalizedStatus = $key;
                            break;
                        }
                    }
                }
            }
            $obra->status_key = $normalizedStatus;
            $obra->status_display = $normalizedStatus && isset($this->statuses[$normalizedStatus])
                ? $this->statuses[$normalizedStatus]
                : ($statusValue !== null ? (string) $statusValue : 'Não informado');

            $obra->descricao_display = $descricaoColumn ? ($obra->{$descricaoColumn} ?? null) : ($obra->descricao ?? null);

            return $obra;
        });
    }

    private function sincronizarValorRecebido(Obra $obra): void
    {
        $valorRecebidoColumn = $this->valorRecebidoColumn();
        if (! $valorRecebidoColumn) {
            return;
        }

        $total = (float) $obra->recebimentos()->sum('valor');
        $obra->update([
            $valorRecebidoColumn => round($total, 2),
        ]);
    }

    private function resetCreateForm(): void
    {
        $this->nome = '';
        $this->status = array_key_first($this->statuses) ?? Obra::STATUS_ANDAMENTO;
        $this->horasTrabalhadas = '';
        $this->descricao = '';
        $this->endereco = '';
    }

    private function normalizeNumber(?string $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $clean = str_replace(["\u{00A0}", ' '], '', $value);
        if (str_contains($clean, ',')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }

        return (float) $clean;
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    private function valorColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['valor_total', 'valor', 'valor_previsto', 'valor_contrato', 'valor_receber']);
    }

    private function horasColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['horas_trabalhadas', 'horas_total', 'total_horas', 'horas']);
    }

    private function statusColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['status', 'situacao']);
    }

    private function descricaoColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['descricao', 'observacoes', 'detalhes']);
    }

    private function enderecoColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['endereco', 'local', 'localizacao']);
    }

    private function valorTotalColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['valor_total', 'valor', 'valor_previsto', 'valor_contrato']);
    }

    private function valorRecebidoColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['valor_recebido', 'valor_pago', 'valor_recebido_total', 'valor_pago_total', 'valor_recebido_atual']);
    }

    private function horasRecebidasColumn(): ?string
    {
        return $this->firstExistingColumn('obras', ['horas_recebidas', 'horas_pag', 'horas_pagas', 'horas_liquidadas']);
    }

    private function statusColumnAllowsString(): bool
    {
        $column = $this->statusColumn();
        if (! $column) {
            return false;
        }

        $meta = $this->tableMeta($column);
        if (! $meta) {
            return false;
        }

        $type = $meta['type'];
        return str_contains($type, 'char') || str_contains($type, 'text') || str_contains($type, 'enum') || str_contains($type, 'set');
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function tableMeta(string $column): ?array
    {
        $this->ensureColumnsMetaLoaded();

        return $this->columnsMeta[$column] ?? null;
    }

    /**
     * @return array<int,array{name:string,type:string,null:bool,default:mixed}>
     */
    private function requiredAdditionalColumns(): array
    {
        $required = [];
        $this->ensureColumnsMetaLoaded();

        $ignore = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            $this->valorColumn(),
            $this->valorTotalColumn(),
            $this->valorRecebidoColumn(),
            $this->horasColumn(),
            $this->horasRecebidasColumn(),
            $this->statusColumnAllowsString() ? $this->statusColumn() : null,
            $this->descricaoColumn(),
            $this->enderecoColumn(),
            'nome',
        ];

        $ignoreFiltered = array_filter($ignore);

        foreach ($this->columnsMeta as $meta) {
            if (in_array($meta['name'], $ignoreFiltered, true)) {
                continue;
            }

            if (! $meta['null'] && $meta['default'] === null) {
                $required[] = $meta;
            }
        }

        return $required;
    }

    private function ensureColumnsMetaLoaded(): void
    {
        if (! empty($this->columnsMeta)) {
            return;
        }

        try {
            $columns = DB::select('SHOW COLUMNS FROM obras');
        } catch (\Throwable $e) {
            $this->columnsMeta = [];
            return;
        }

        foreach ($columns as $col) {
            $this->columnsMeta[$col->Field] = [
                'name' => $col->Field,
                'type' => strtolower((string) $col->Type),
                'null' => $col->Null === 'YES',
                'default' => $col->Default,
            ];
        }
    }

    private function defaultForType(string $type)
    {
        if (str_contains($type, 'int')) {
            return 0;
        }

        if (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
            return 0;
        }

        if (str_contains($type, 'datetime') || str_contains($type, 'timestamp')) {
            return now()->toDateTimeString();
        }

        if (str_contains($type, 'date')) {
            return now()->toDateString();
        }

        if (str_starts_with($type, 'enum(')) {
            $values = array_map(function ($v) {
                return trim($v, "'\"");
            }, explode(',', substr($type, 5, -1)));
            return $values[0] ?? '';
        }

        return '';
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

    private function normalizedStatusArray(array $statuses): array
    {
        $normalized = [];
        foreach ($statuses as $key => $label) {
            $normalized[$this->normalizeStatusKey($label)] = $label;
        }

        return $normalized;
    }
}
