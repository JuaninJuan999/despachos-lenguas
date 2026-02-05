<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_id')->constrained('despachos')->onDelete('cascade');
            $table->string('codigo_producto'); // 2601-11413-1001
            $table->string('descripcion_producto')->nullable(); // Media Canal 1
            $table->decimal('peso_frio', 10, 2)->nullable();
            $table->decimal('peso_caliente', 10, 2)->nullable();
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->string('decomisos')->nullable();
            $table->text('destino_especifico'); // TEMP1 / 05 REAL DE MINAS / 05301 / DIAGONAL 16#57-169...
            $table->dateTime('fecha_beneficio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_productos');
    }
};
