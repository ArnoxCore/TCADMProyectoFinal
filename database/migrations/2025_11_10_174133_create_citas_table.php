<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('mecanico_id')->nullable()
                  ->constrained('mecan  icos')->cascadeOnUpdate()->nullOnDelete();

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->enum('estatus', ['pendiente','confirmada','en_proceso','completada','cancelada'])->default('pendiente');
            $table->text('observaciones_cliente')->nullable();
            $table->decimal('precio_final', 10, 2)->nullable(); // se calcula (servicios + extras aprobados)

            $table->timestamps();
            $table->softDeletes();

            $table->index('fecha');                 // idx_fecha
            $table->index(['mecanico_id','fecha']); // idx_mecanico_fecha
            $table->index(['fecha','hora_inicio']); // idx_fecha_hora
            $table->index('cliente_id');            // idx_cliente
            $table->index('estatus');               // idx_estatus
        });
    }
    public function down(): void {
        Schema::dropIfExists('citas');
    }
};
