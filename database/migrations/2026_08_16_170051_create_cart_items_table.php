<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            // Price snapshot at the time the product was added.
            $table->decimal('unit_price', 12, 2);

            // Price of selected modifiers/options per single item.
            $table->decimal('modifiers_price', 12, 2)->default(0);

            // Final line subtotal.
            $table->decimal('subtotal', 12, 2);

            // Snapshot of selected modifiers.
            $table->json('selected_modifiers')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('cart_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};