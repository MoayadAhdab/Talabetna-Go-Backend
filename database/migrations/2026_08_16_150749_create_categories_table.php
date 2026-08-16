<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Parent business
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Category information
            $table->string('name');
            $table->string('slug');

            $table->text('description')->nullable();

            // Branding
            $table->string('image')->nullable();

            // Status / ordering
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // Additional flexible settings
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('business_id');
            $table->index('is_active');

            $table->unique(
                ['business_id', 'slug'],
                'categories_business_id_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};