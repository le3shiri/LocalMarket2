<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80); // e.g. "Tienda Barcelona", "Almacén Central"
            $table->string('type', 20); // warehouse | store
            $table->string('address')->nullable();
            $table->boolean('is_online_source')->default(false)->index(); // Online orders fulfilled from here (BR-INV-03 / AS-01)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
