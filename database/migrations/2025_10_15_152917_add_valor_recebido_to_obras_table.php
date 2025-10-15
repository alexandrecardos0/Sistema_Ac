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
        Schema::table('obras', function (Blueprint $table) {
            if (! Schema::hasColumn('obras', 'valor_recebido')) {
                $table->decimal('valor_recebido', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('obras', 'valor_total')) {
                $table->decimal('valor_total', 12, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obras', function (Blueprint $table) {
            if (Schema::hasColumn('obras', 'valor_total')) {
                $table->dropColumn('valor_total');
            }

            if (Schema::hasColumn('obras', 'valor_recebido')) {
                $table->dropColumn('valor_recebido');
            }
        });
    }
};
