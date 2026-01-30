<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 購入者
            $table->foreignId('item_id')->constrained()->cascadeOnDelete(); // 商品
            $table->integer('price'); // 購入時価格

            $table->string('postal_code', 20);
            $table->string('address', 255);
            $table->string('name', 100); // 受取人氏名
            $table->string('payment_method', 20);

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};