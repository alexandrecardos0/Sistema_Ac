<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroHoras extends Model
{
    use HasFactory;

    protected $fillable = [
        'funcionario_id',
        'mes',
        'ano',
        'dias_selecionados',
        'valor_hora',
        'vale',
        'vales_detalhes',
        'total',
        'status',
        'periodo_inicio',
        'periodo_fim',
    ];

    protected $casts = [
        'dias_selecionados' => 'array',
        'vales_detalhes' => 'array',
        'periodo_inicio' => 'date',
        'periodo_fim' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
