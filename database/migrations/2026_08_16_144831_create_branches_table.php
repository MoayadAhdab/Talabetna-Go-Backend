<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            // Parent business
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Basic information
            $table->string('name');
            $table->string('slug');

            $table->text('description')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Location
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Delivery
            $table->decimal('delivery_radius_km', 8, 2)
                ->nullable();

            $table->decimal('minimum_order_amount', 10, 2)
                ->default(0);

            $table->decimal('delivery_fee', 10, 2)
                ->default(0);

            // Branch status
            $table->boolean('is_active')
                ->default(true);

            $table->boolean('is_accepting_orders')
                ->default(true);

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedInteger('sort_order')
                ->default(0);

            // Working hours and additional settings
            $table->json('working_hours')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('business_id');
            $table->index('city');
            $table->index('area');
            $table->index('is_active');
            $table->index('is_accepting_orders');

            $table->unique(
                ['business_id', 'slug'],
                'branches_business_id_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};