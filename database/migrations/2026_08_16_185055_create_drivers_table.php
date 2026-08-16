<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();

            $table->string('avatar')->nullable();

            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();

            $table->string('status')->default('offline');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamp('last_location_at')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};