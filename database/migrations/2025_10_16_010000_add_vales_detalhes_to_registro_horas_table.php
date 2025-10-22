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
            $table->json('vales_detalhes')->nullable()->after('vale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_horas', function (Blueprint $table) {
            $table->dropColumn('vales_detalhes');
        });
    }
};
