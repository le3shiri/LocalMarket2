<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique(); // LM-YYYYMMDD-XXXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Statuses (BR-ORD-04)
            // pending_payment | confirmed | preparing | ready_for_pickup | out_for_delivery | completed | cancelled
            $table->string('status', 30)->default('pending_payment')->index();

            // Payment statuses (BR-PAY-01)
            // pending | paid | failed | refunded
            $table->string('payment_status', 30)->default('pending')->index();

            // Fulfilment method: delivery | pickup (FR-DEL-01)
            $table->string('fulfilment_method', 20)->default('delivery');

            // Amounts in EUR
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            // Optional Coupon reference
            $table->unsignedBigInteger('coupon_id')->nullable()->index();
            $table->string('coupon_code', 40)->nullable();

            // Customer snapshot at order time
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('phone', 30)->nullable();

            // Delivery address snapshot
            $table->string('address')->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('city', 50)->nullable()->default('Barcelona');
            $table->text('notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Snapshots at purchase time (BR-ORD-02)
            $table->string('product_name');
            $table->string('sale_unit', 20);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('quantity', 10, 3);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // null = system/webhook
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
