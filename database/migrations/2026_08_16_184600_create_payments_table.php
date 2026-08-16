<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('method');

            $table->string('status')->default('pending');

            $table->decimal('amount', 12, 2);

            $table->string('currency', 10)->default('USD');

            $table->string('transaction_id')->nullable();
            $table->string('reference')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->text('failure_reason')->nullable();

            $table->json('gateway_response')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('method');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};