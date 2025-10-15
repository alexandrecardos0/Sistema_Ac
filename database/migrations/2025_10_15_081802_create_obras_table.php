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
        if (Schema::hasTable('obras')) {
            return;
        }

        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('status', 20)->default('andamento');
            $table->decimal('horas_trabalhadas', 10, 2)->default(0);
            $table->decimal('valor_receber', 12, 2)->default(0);
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};
