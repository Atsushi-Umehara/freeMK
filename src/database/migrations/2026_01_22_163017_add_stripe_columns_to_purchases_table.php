<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Stripe Checkout Session ID（冪等性の要）
            $table->string('stripe_session_id')->nullable()->unique()->after('payment_method');

            // 決済状態（pending / paid / failed など）
            $table->string('payment_status')->default('pending')->after('stripe_session_id');

            // Stripe payment_intent（あれば）
            $table->string('stripe_payment_intent_id')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['stripe_session_id']);
            $table->dropColumn(['stripe_session_id', 'payment_status', 'stripe_payment_intent_id']);
        });
    }
};