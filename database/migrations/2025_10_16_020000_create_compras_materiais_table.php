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
        Schema::create('compras_materiais', function (Blueprint $table) {
            $table->id();
            $table->string('material');
            $table->unsignedInteger('quantidade')->default(1);
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->date('data_compra')->nullable();
            $table->string('fornecedor')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras_materiais');
    }
};
