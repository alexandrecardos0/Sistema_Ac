<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvariaMaterial extends Model
{
    use HasFactory;

    protected $table = 'avarias_materiais';

    protected $fillable = [
        'material',
        'valor_conserto',
        'descricao',
    ];

    protected $casts = [
        'valor_conserto' => 'decimal:2',
    ];
}
