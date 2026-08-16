<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');

            $table->text('description')->nullable();

            $table->boolean('is_required')->default(false);

            $table->unsignedInteger('min_selections')->default(0);
            $table->unsignedInteger('max_selections')->default(1);

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('business_id');
            $table->index('is_active');

            $table->unique(
                ['business_id', 'slug'],
                'modifier_groups_business_id_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_groups');
    }
};