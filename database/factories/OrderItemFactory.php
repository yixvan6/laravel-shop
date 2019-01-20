<?php

use Faker\Generator as Faker;
use App\Models\Product;
use App\Models\OrderItem;

$factory->define(OrderItem::class, function (Faker $faker) {
    $product = Product::query()->where('on_sale', true)->inRandomOrder()->first();
    // 从该商品的 SKU 中随机取一条
    $sku = $product->skus()->where('stock', '>=', '5')->inRandomOrder()->first();

    return [
        'amount'         => random_int(1, 5), // 购买数量随机 1 - 5 份
        'price'          => $sku->price,
        'rating'         => null,
        'review'         => null,
        'reviewed_at'    => null,
        'product_id'     => $product->id,
        'product_sku_id' => $sku->id,
    ];
});
