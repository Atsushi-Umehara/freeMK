<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // コメントしたユーザー
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // コメント対象の商品
            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();

            // コメント本文
            $table->string('body', 255);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}