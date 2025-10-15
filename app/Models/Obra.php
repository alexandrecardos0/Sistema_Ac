<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'horas_trabalhadas' => 'decimal:2',
        'valor_receber' => 'decimal:2',
    ];

    public const STATUS_ANDAMENTO = 'andamento';
    public const STATUS_CONCLUIDA = 'concluida';

    public static function statuses(): array
    {
        return [
            self::STATUS_ANDAMENTO => 'Em Andamento',
            self::STATUS_CONCLUIDA => 'Concluída',
        ];
    }
}
