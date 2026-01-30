<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Stripe Checkout Session ID は重複させない（冪等性）
            $table->unique('stripe_session_id', 'purchases_stripe_session_id_unique');

            // PaymentIntent ID も重複させない（nullableでもOK。MySQLはNULLは重複扱いにならない）
            $table->unique('stripe_payment_intent_id', 'purchases_stripe_payment_intent_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_stripe_session_id_unique');
            $table->dropUnique('purchases_stripe_payment_intent_id_unique');
        });
    }
};