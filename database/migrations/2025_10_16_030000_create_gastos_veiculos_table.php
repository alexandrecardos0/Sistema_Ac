<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gastos_veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // gasolina, manutencao, etc.
            $table->string('veiculo')->nullable();
            $table->decimal('valor', 12, 2);
            $table->date('data_gasto')->nullable();
            $table->unsignedInteger('km')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_veiculos');
    }
};
