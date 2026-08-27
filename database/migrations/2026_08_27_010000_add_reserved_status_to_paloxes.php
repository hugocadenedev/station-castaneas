<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paloxes', function (Blueprint $table) {
            $table->enum('availability_status', ['available', 'partial', 'reserved', 'exhausted'])->default('available')->change();
        });
    }

    public function down(): void
    {
        Schema::table('paloxes', function (Blueprint $table) {
            $table->enum('availability_status', ['available', 'partial', 'exhausted'])->default('available')->change();
        });
    }
};