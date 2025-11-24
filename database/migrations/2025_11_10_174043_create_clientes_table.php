<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()
                  ->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('direccion', 255)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('clientes');
    }
};