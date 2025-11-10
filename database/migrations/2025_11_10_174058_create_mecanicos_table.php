<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mecanicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()
                  ->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('numero_empleado', 50)->unique();
            $table->string('especialidad', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('mecanicos');
    }
};