<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            // Business Type
            $table->foreignId('business_type_id')
                ->constrained('business_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Branding
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Location
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Business Settings
            $table->decimal('commission_rate', 5, 2)
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->json('settings')
                ->nullable();

            $table->timestamps();

            $table->index('business_type_id');
            $table->index('city');
            $table->index('is_active');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};