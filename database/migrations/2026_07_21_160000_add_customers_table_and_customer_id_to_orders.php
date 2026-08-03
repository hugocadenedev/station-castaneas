<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('reference_code')->nullable()->unique();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->index(['customer_id', 'ordered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};