<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Keep relation to current product when possible.
             * Nullable because products may be deleted later.
             */
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            /*
             * Product snapshot.
             */
            $table->string('product_name');
            $table->string('product_sku')->nullable();

            /*
             * Pricing snapshot.
             */
            $table->decimal('unit_price', 12, 2);
            $table->decimal('modifiers_price', 12, 2)->default(0);

            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('subtotal', 12, 2);

            /*
             * Snapshot of selected modifiers.
             */
            $table->json('selected_modifiers')->nullable();

            /*
             * Optional item note.
             */
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};