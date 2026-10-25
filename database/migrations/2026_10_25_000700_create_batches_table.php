<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('lot_code', 50); // Batch identifier from supplier
            $table->date('expiry_date')->index(); // Best before / expiration date
            $table->decimal('quantity', 10, 3)->default(0);
            $table->timestamps();

            $table->index(['product_id', 'location_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
