<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraMaterial extends Model
{
    use HasFactory;

    protected $table = 'compras_materiais';

    protected $fillable = [
        'material',
        'quantidade',
        'valor_total',
        'data_compra',
        'fornecedor',
        'observacoes',
    ];

    protected $casts = [
        'data_compra' => 'date',
        'quantidade' => 'integer',
        'valor_total' => 'float',
    ];
}
