<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSku;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建 30 个商品
        $products = factory(Product::class, 30)->create();
        // 每个商品创建 3 个 sku
        foreach ($products as $product) {
            $skus = factory(ProductSku::class, 3)->create(['product_id' => $product->id]);
            // 根据 skus 计算出每个商品的 price
            $product->update(['price' => $skus->min('price')]);
        }
    }
}
