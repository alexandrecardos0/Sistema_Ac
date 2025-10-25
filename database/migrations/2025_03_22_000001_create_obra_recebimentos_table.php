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
        Schema::create('obra_recebimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->date('data')->nullable();
            $table->decimal('valor', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_recebimentos');
    }
};
