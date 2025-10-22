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
        Schema::table('registro_horas', function (Blueprint $table) {
            $table->string('status')->default('closed')->after('total');
            $table->date('periodo_inicio')->nullable()->after('status');
            $table->date('periodo_fim')->nullable()->after('periodo_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_horas', function (Blueprint $table) {
            $table->dropColumn(['periodo_fim', 'periodo_inicio', 'status']);
        });
    }
};
