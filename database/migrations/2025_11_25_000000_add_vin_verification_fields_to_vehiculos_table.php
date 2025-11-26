<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->boolean('vin_verificado')->default(false)->after('vin');
            $table->string('vin_detected_marca', 100)->nullable()->after('vin_verificado');
            $table->string('vin_detected_modelo', 150)->nullable()->after('vin_detected_marca');
            $table->unsignedSmallInteger('vin_detected_ano')->nullable()->after('vin_detected_modelo');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn([
                'vin_verificado',
                'vin_detected_marca',
                'vin_detected_modelo',
                'vin_detected_ano',
            ]);
        });
    }
};
