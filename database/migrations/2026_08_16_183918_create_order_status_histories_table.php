<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('from_status')->nullable();
            $table->string('to_status');

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('to_status');
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};