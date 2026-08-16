<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('modifier_group_id')
                ->constrained('modifier_groups')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');

            $table->decimal('price', 10, 2)->default(0);

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('modifier_group_id');
            $table->index('is_active');

            $table->unique(
                ['modifier_group_id', 'slug'],
                'modifier_options_group_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_options');
    }
};