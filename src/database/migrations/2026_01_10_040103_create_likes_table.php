<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // いいねした人
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // いいねされた商品
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            // 同じユーザーが同じ商品を二重いいねできないように
            $table->unique(['user_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('likes');
    }
}