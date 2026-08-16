<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Basic information
            $table->string('name');
            $table->string('slug');

            $table->string('sku')->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();

            // Product image
            $table->string('image')->nullable();

            // Product settings
            $table->integer('preparation_time_minutes')
                ->nullable();

            $table->boolean('is_available')
                ->default(true);

            $table->boolean('is_featured')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('business_id');
            $table->index('category_id');
            $table->index('is_available');
            $table->index('is_active');
            $table->index('is_featured');

            $table->unique(
                ['business_id', 'slug'],
                'products_business_id_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};