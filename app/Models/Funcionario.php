<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funcionario extends Model
{
    protected $fillable = ['nome'];

    public function registroHoras(): HasMany
    {
        return $this->hasMany(RegistroHoras::class);
    }

    public function getTotalRecebidoNoMes(int $ano, int $mes): float
    {
        return (float) $this->registroHoras()
            ->whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->sum('total');
    }
}
