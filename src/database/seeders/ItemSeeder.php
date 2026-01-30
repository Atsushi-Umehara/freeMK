<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // 画像は storage/app/public/items の中にある前提
        // DBには "items/ファイル名" を保存する（asset('storage/'.$image_path) で表示できる）

        $items = [
            [
                'title' => 'Armani Mens Clock',
                'image_path' => 'items/Armani+Mens+Clock (1).jpg',
                'price' => 12000,
            ],
            [
                'title' => 'HDD Hard Disk',
                'image_path' => 'items/HDD+Hard+Disk.jpg',
                'price' => 3800,
            ],
            [
                'title' => 'i Love! MG',
                'image_path' => 'items/iLoveIMG+d.jpg',
                'price' => 1500,
            ],
            [
                'title' => 'Leather Shoes',
                'image_path' => 'items/Leather+Shoes+Product+Photo.jpg',
                'price' => 6800,
            ],
            [
                'title' => 'Living Room Laptop',
                'image_path' => 'items/Living+Room+Laptop.jpg',
                'price' => 9800,
            ],
            [
                'title' => 'Music Mic',
                'image_path' => 'items/Music+Mic+4632231.jpg',
                'price' => 2400,
            ],
            [
                'title' => 'Purse Fashion',
                'image_path' => 'items/Purse+fashion+pocket.jpg',
                'price' => 4200,
            ],
            [
                'title' => 'Tumbler Souvenir',
                'image_path' => 'items/Tumbler+souvenir.jpg',
                'price' => 1800,
            ],
            [
                'title' => 'Waitress with Coffee Grinder',
                'image_path' => 'items/Waitress+with+Coffee+Grinder.jpg',
                'price' => 3600,
            ],
            [
                'title' => '外出メイクアップセット',
                'image_path' => 'items/外出メイクアップセット.jpg',
                'price' => 2900,
            ],
            // 画像なしの確認用
            [
                'title' => '画像なし商品（テスト）',
                'image_path' => null,
                'price' => 1000,
            ],
        ];

        foreach ($items as $data) {
            Item::create([
                'user_id'     => 1, // さっき作ったユーザーが id=1 ならOK
                'title'       => $data['title'],
                'description' => 'ダミーデータです（Seeder投入）',
                'price'       => $data['price'],
                'status'      => 'on_sale',
                'image_path'  => $data['image_path'],
            ]);
        }
    }
}