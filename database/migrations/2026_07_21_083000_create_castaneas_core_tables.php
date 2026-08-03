<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fruits', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('varieties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fruit_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['fruit_id', 'name']);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ggn_code')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tare_types', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->decimal('weight_kg', 8, 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('calibers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fruit_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['fruit_id', 'name']);
        });

        Schema::create('receptions', function (Blueprint $table) {
            $table->id();
            $table->string('reception_number')->unique();
            $table->timestamp('received_at');
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fruit_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('variety_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('received_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('gross_weight_kg', 10, 3);
            $table->enum('conformity_status', ['conforming', 'non_conforming'])->default('conforming');
            $table->text('non_conformity_reason')->nullable();
            $table->enum('processing_status', ['pending', 'calibrated', 'stocked_non_conforming'])->default('pending');
            $table->timestamps();

            $table->index(['conformity_status', 'processing_status']);
        });

        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('caliber_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('tare_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('performed_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('calibrated_at');
            $table->decimal('net_weight_kg', 10, 3);
            $table->decimal('waste_weight_kg', 10, 3)->default(0);
            $table->timestamps();

            $table->index('calibrated_at');
        });

        Schema::create('paloxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('calibration_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('palox_number')->unique();
            $table->decimal('initial_net_weight_kg', 10, 3);
            $table->decimal('remaining_net_weight_kg', 10, 3);
            $table->boolean('under_contract')->default(false);
            $table->enum('availability_status', ['available', 'partial', 'exhausted'])->default('available');
            $table->timestamp('labeled_at');
            $table->timestamps();

            $table->index(['availability_status', 'under_contract']);
        });

        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('order_number')->unique();
            $table->timestamp('ordered_at');
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['client_name', 'ordered_at']);
        });

        Schema::create('customer_order_palox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('palox_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('picked_net_weight_kg', 10, 3);
            $table->timestamps();

            $table->unique(['customer_order_id', 'palox_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_order_palox');
        Schema::dropIfExists('customer_orders');
        Schema::dropIfExists('paloxes');
        Schema::dropIfExists('calibrations');
        Schema::dropIfExists('receptions');
        Schema::dropIfExists('calibers');
        Schema::dropIfExists('tare_types');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('varieties');
        Schema::dropIfExists('fruits');
    }
};