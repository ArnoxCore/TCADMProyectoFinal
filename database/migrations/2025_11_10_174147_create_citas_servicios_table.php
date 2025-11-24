<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('citas_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnUpdate()->restrictOnDelete();

            
            $table->decimal('precio_unitario', 10, 2)->nullable();

            $table->timestamps();
            $table->unique(['cita_id','servicio_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('citas_servicios');
    }
};