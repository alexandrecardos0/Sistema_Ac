<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoVeiculo extends Model
{
    use HasFactory;

    protected $table = 'gastos_veiculos';

    protected $fillable = [
        'tipo',
        'veiculo',
        'valor',
        'data_gasto',
        'km',
        'descricao',
    ];

    protected $casts = [
        'data_gasto' => 'date',
        'valor' => 'float',
        'km' => 'integer',
    ];
}
