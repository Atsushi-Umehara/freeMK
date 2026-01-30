<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCommentsTable extends Migration
{
    public function up()
    {
        // すでに user_id / item_id / body も外部キーも存在しているため
        // このマイグレーションでは何もしない（migrate を通すための処置）
    }

    public function down()
    {
        // 何もしない
    }
}