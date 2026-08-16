<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');

            $table->text('description')->nullable();

            /*
             * percentage = 10% / 20%
             * fixed      = $5 / $10
             */
            $table->string('type');

            $table->decimal('value', 10, 2);

            /*
             * Maximum discount for percentage coupons.
             */
            $table->decimal('max_discount', 12, 2)->nullable();

            /*
             * Coupon is only valid when subtotal reaches this amount.
             */
            $table->decimal('minimum_order_amount', 12, 2)
                ->default(0);

            /*
             * Global usage limit.
             */
            $table->unsignedInteger('usage_limit')->nullable();

            $table->unsignedInteger('usage_count')->default(0);

            /*
             * Optional per-customer usage limit.
             */
            $table->unsignedInteger('per_customer_limit')
                ->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};