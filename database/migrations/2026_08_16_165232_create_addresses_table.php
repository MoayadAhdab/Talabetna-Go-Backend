<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('label');

            $table->string('contact_name')
                ->nullable();

            $table->string('contact_phone')
                ->nullable();

            $table->string('address_line');

            $table->string('building')
                ->nullable();

            $table->string('floor')
                ->nullable();

            $table->string('apartment')
                ->nullable();

            $table->string('city')
                ->nullable();

            $table->string('area')
                ->nullable();

            $table->text('delivery_instructions')
                ->nullable();

            $table->decimal('latitude', 10, 7)
                ->nullable();

            $table->decimal('longitude', 10, 7)
                ->nullable();

            $table->boolean('is_default')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('customer_id');
            $table->index('city');
            $table->index('area');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};