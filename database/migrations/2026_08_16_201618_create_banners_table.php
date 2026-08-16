<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();

            $table->string('image');
            $table->string('mobile_image')->nullable();

            /*
             * top, home, category, merchant
             */
            $table->string('placement')->default('top');

            /*
             * merchant, category, product, coupon, url, none
             */
            $table->string('link_type')->default('none');

            $table->string('link_value')->nullable();

            $table->foreignId('business_id')
                ->nullable()
                ->constrained('businesses')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('placement');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};