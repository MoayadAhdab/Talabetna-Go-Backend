<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            /*
             * Human-readable order number.
             * Example: TG-20260816-000001
             */
            $table->string('order_number')->unique();

            /*
             * Customer / Branch references.
             */
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            /*
             * Optional source cart.
             */
            $table->foreignId('cart_id')
                ->nullable()
                ->constrained('carts')
                ->nullOnDelete();

            /*
             * Order status.
             */
            $table->string('status')->default('pending');

            /*
             * Payment status.
             */
            $table->string('payment_status')->default('pending');

            /*
             * Delivery status.
             */
            $table->string('delivery_status')->default('pending');

            /*
             * Payment method.
             */
            $table->string('payment_method')->nullable();

            /*
             * Customer requested notes.
             */
            $table->text('customer_note')->nullable();

            /*
             * Price snapshot.
             */
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            /*
             * Address snapshot.
             *
             * Important:
             * We keep the address as it was at checkout.
             * The customer can later edit/delete their address
             * without affecting this historical order.
             */
            $table->json('delivery_address');

            /*
             * Branch snapshot.
             */
            $table->json('branch_snapshot');

            /*
             * Additional metadata.
             */
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index('branch_id');
            $table->index('cart_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('delivery_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};