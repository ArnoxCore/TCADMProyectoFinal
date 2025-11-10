<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notificaciones_enviadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->cascadeOnUpdate()->nullOnDelete();
            $table->enum('tipo', ['confirmacion','recordatorio','cancelacion','completado']);
            $table->string('asunto', 255);
            $table->longText('mensaje');
            $table->timestamp('enviado_at');
            $table->timestamps();

            $table->index(['user_id','tipo']); // idx_user_tipo
            $table->index('cita_id');          // idx_cita
        });
    }
    public function down(): void {
        Schema::dropIfExists('notificaciones_enviadas');
    }
};