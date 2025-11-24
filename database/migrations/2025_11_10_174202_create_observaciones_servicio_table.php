<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('observaciones_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('mecanico_id')->constrained('mecanicos')->cascadeOnUpdate()->restrictOnDelete();

            // Tipos sencillos
            $table->enum('tipo', ['observacion','recomendacion','falla'])->default('observacion');

            // Texto libre
            $table->text('observacion');

            // Para el flujo de “falla -> cliente aprueba o no -> sumar al total”
            $table->boolean('requiere_reparacion')->default(false);
            $table->boolean('aprobada')->nullable(); // null = pendiente; true/false = decisión del cliente
            $table->timestamp('aprobada_at')->nullable();
            $table->decimal('costo_pieza', 10, 2)->nullable();
            $table->decimal('costo_mano_obra', 10, 2)->nullable();

            $table->timestamps();
            $table->index('cita_id'); // idx_cita
        });
    }
    public function down(): void {
        Schema::dropIfExists('observaciones_servicio');
    }
};