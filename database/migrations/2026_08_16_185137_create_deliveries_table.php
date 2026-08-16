<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            $table->string('status')->default('pending');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->text('failure_reason')->nullable();

            $table->decimal('pickup_latitude', 10, 7)->nullable();
            $table->decimal('pickup_longitude', 10, 7)->nullable();

            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();

            $table->decimal('delivery_fee', 12, 2)->default(0);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('driver_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};