<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('status')->default('active');

            $table->unsignedInteger('items_count')->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index('branch_id');
            $table->index('status');

            $table->unique(
                ['customer_id', 'branch_id', 'status'],
                'carts_customer_branch_status_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};