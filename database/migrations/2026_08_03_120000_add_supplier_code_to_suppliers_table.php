<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supplier_code')->nullable()->after('name');
        });

        DB::table('suppliers')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $supplier): void {
                DB::table('suppliers')
                    ->where('id', $supplier->id)
                    ->update(['supplier_code' => sprintf('FOU-%03d', $supplier->id)]);
            });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->unique('supplier_code');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique(['supplier_code']);
            $table->dropColumn('supplier_code');
        });
    }
};