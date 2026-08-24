<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receptions', function (Blueprint $table) {
            $table->decimal('gross_weight_kg', 10, 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('receptions', function (Blueprint $table) {
            $table->decimal('gross_weight_kg', 10, 3)->nullable(false)->default(0)->change();
        });
    }
};