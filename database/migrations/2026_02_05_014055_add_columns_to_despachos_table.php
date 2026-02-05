<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            // Renombrar plaza_remolque a placa_remolque
            $table->renameColumn('plaza_remolque', 'placa_remolque');
        });

        Schema::table('despachos', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->string('destino_general')->nullable()->after('conductor');
            $table->dateTime('fecha_expedicion')->nullable()->after('destino_general');
            $table->string('archivo_original')->nullable()->after('fecha_expedicion');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->after('archivo_original');
        });
    }

    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn(['destino_general', 'fecha_expedicion', 'archivo_original', 'usuario_id']);
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->renameColumn('placa_remolque', 'plaza_remolque');
        });
    }
};
