<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Payment Provider: mock | stripe (BR-PAY-01)
            $table->string('provider', 20)->default('mock');

            // Provider Transaction / PaymentIntent ID (e.g., pi_xxx or mock_xxx)
            $table->string('transaction_id', 100)->unique()->nullable();

            // Status: pending | completed | failed | refunded
            $table->string('status', 30)->default('pending')->index();

            // Currency & Amount in EUR
            $table->string('currency', 3)->default('EUR');
            $table->decimal('amount', 10, 2);

            // Method details (e.g. card, bizum, stripe_card)
            $table->string('payment_method', 50)->default('card');
            $table->string('card_last4', 4)->nullable();
            $table->string('card_brand', 30)->nullable();

            // Optional raw payload / response for audit and debugging
            $table->json('payload')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
