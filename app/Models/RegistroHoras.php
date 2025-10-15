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
        'total',
    ];

    protected $casts = [
        'dias_selecionados' => 'array',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
