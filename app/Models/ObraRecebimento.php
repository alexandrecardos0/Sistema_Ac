<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObraRecebimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'obra_id',
        'valor',
        'data',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
