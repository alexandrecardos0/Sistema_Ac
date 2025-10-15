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
        if (Schema::hasTable('relatorios_mensais')) {
            return;
        }

        Schema::create('relatorios_mensais', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->json('data');
            $table->timestamps();
            $table->unique(['ano', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relatorios_mensais');
    }
};
