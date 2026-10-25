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
            $table->string('code', 30)->unique();
            $table->string('description')->nullable();

            // Discount type: percentage | fixed (BR-PRO-01)
            $table->string('type', 20)->default('percentage');
            $table->decimal('value', 10, 2); // 10.00 for 10% or 5.00 for 5.00 EUR

            // Minimum cart subtotal required to apply coupon (EUR)
            $table->decimal('min_order_amount', 10, 2)->default(0.00);

            // Usage limits (BR-PRO-02)
            $table->unsignedInteger('max_uses')->nullable(); // null = unlimited
            $table->unsignedInteger('uses_count')->default(0);

            // Validity dates (BR-PRO-03)
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
