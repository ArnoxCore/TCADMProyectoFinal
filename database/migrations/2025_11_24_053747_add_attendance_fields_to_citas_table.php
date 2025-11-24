<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->timestamp('check_in_at')
                ->nullable()
                ->after('hora_fin');

            $table->timestamp('inicio_real_at')
                ->nullable()
                ->after('check_in_at');

            $table->boolean('asistio')
                ->nullable()
                ->after('inicio_real_at');

            $table->index('check_in_at');
            $table->index('inicio_real_at');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex(['check_in_at']);
            $table->dropIndex(['inicio_real_at']);
            $table->dropColumn(['check_in_at', 'inicio_real_at', 'asistio']);
        });
    }
};