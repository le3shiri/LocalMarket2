<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('price', 10, 2); // EUR with 2 decimals
            $table->string('sale_unit', 20)->default('unit'); // unit | kg | pack (BR-INV-05 / AS-05)
            $table->string('shelf_location', 50)->nullable(); // e.g. "Pasillo 3, Estante B"
            $table->boolean('tracks_expiry')->default(false); // Perishable lot tracking (BR-INV-10)
            $table->decimal('low_stock_threshold', 10, 3)->default(5.000);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
