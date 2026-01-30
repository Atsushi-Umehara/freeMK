<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'user_id'     => 1, // さっき作ったユーザー（id=1）に紐付け
            'title'       => $this->faker->words(3, true),      // 適当な商品名
            'description' => $this->faker->sentence(10),        // 説明
            'price'       => $this->faker->numberBetween(500, 10000), // 価格
            'status'      => 'on_sale',                         // 販売中
        ];
    }
}