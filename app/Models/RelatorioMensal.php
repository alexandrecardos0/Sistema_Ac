<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelatorioMensal extends Model
{
    use HasFactory;

    protected $table = 'relatorios_mensais';

    protected $fillable = [
        'ano',
        'mes',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getMesFormatadoAttribute(): string
    {
        $meses = [
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

        return ($meses[$this->mes] ?? $this->mes) . ' / ' . $this->ano;
    }
}
