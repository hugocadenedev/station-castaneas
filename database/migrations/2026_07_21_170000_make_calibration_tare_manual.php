<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->decimal('tare_weight_kg', 10, 3)->default(0)->after('tare_type_id');
        });

        DB::statement('UPDATE calibrations SET tare_weight_kg = COALESCE((SELECT weight_kg FROM tare_types WHERE tare_types.id = calibrations.tare_type_id), 0)');
    }

    public function down(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropColumn('tare_weight_kg');
        });
    }
};