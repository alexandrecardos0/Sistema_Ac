<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Comprovante de Pagamento</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2933; margin: 0; padding: 24px; }
        h1, h2, h3 { margin: 0 0 12px; }
        h1 { font-size: 20px; }
        h2 { font-size: 16px; margin-top: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 600; }
        .totais { margin-top: 16px; }
        .totais td { border: none; padding: 4px 0; }
        .text-right { text-align: right; }
        .muted { color: #6b7280; font-size: 11px; }
    </style>
</head>
<body>
    @if(!empty($logoBase64))
        <div style="text-align: center; margin-bottom: 16px;">
            <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 80px;">
        </div>
    @endif

    <h1>Comprovante de Pagamento</h1>
    <p class="muted">Gerado em {{ now()->format('d/m/Y H:i') }}</p>

    <section>
        <h2>Funcionário</h2>
        <p><strong>Nome:</strong> {{ $funcionario->nome }}</p>
        @if(isset($pagamento['periodo_inicio'], $pagamento['periodo_fim']) && $pagamento['periodo_inicio'] && $pagamento['periodo_fim'])
            <p><strong>Período:</strong> {{ $pagamento['periodo_inicio'] }} até {{ $pagamento['periodo_fim'] }}</p>
        @endif
        <p><strong>Identificador:</strong> {{ $pagamento['label'] ?? 'Pagamento' }}</p>
    </section>

    <section>
        <h2>Horas Trabalhadas</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horas</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagamento['dias'] as $dia)
                    <tr>
                        <td>{{ $dia['data'] }}</td>
                        <td>{{ number_format($dia['horas'], 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($dia['valor'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Vales / Adiantamentos</h2>
        @if(!empty($pagamento['vales']))
            <table>
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Dia/Mês</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagamento['vales'] as $vale)
                        @php
                            $dia = isset($vale['dia']) && $vale['dia'] ? str_pad((string) $vale['dia'], 2, '0', STR_PAD_LEFT) : '—';
                            $mes = isset($vale['mes']) && $vale['mes'] ? str_pad((string) $vale['mes'], 2, '0', STR_PAD_LEFT) : '—';
                        @endphp
                        <tr>
                            <td>{{ trim($vale['descricao'] ?? '') !== '' ? $vale['descricao'] : 'Sem descrição' }}</td>
                            <td>{{ $dia }}/{{ $mes }}</td>
                            <td>R$ {{ number_format($vale['valor'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Não há vales vinculados a este pagamento.</p>
        @endif
    </section>

    <section class="totais">
        <table>
            <tr>
                <td><strong>Total de horas</strong></td>
                <td class="text-right">{{ number_format($pagamento['total_horas'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Valor por hora</strong></td>
                <td class="text-right">R$ {{ number_format($pagamento['valor_hora'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total de vales</strong></td>
                <td class="text-right">R$ {{ number_format($pagamento['total_vales'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total a receber</strong></td>
                <td class="text-right"><strong>R$ {{ number_format($pagamento['total'], 2, ',', '.') }}</strong></td>
            </tr>
        </table>
    </section>
</body>
</html>
