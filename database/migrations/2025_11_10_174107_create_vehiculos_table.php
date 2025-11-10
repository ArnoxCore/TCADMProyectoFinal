<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                  ->constrained('clientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->year('ano');
            $table->string('placa', 20)->unique();
            $table->string('vin', 17)->unique()->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('kilometraje')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id'); // idx_cliente
            $table->index('placa');      // idx_placa
        });
    }
    public function down(): void {
        Schema::dropIfExists('vehiculos');
    }
};