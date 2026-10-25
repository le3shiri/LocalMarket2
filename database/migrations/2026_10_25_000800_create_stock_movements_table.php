<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index(); // Will relate to orders table in Sprint 4
            // receipt | transfer_out | transfer_in | adjustment | store_sale | reservation | release | sale | return
            $table->string('type', 30)->index();
            $table->decimal('quantity', 10, 3); // Signed delta (+ or -)
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['product_id', 'location_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
