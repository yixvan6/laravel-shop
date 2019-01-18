<?php

namespace App\Services;

use App\Models\CartItem;

class CartService
{
    public function get()
    {
        return \Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add($sku_id, $amount)
    {
        $user = \Auth::user();

        // 检查购物车中是否已存在该商品，若存在只增加数量
        if ($item = $user->cartItems()->where('product_sku_id', $sku_id)->first()) {
            $item->update(['amount' => $item->amount + $amount]);
        } else {
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($sku_id);
            $item->save();
        }
    }

    public function remove($sku_ids)
    {
        if (! is_array($sku_ids)) {
            $sku_ids = [$sku_ids];
        }
        \Auth::user()->cartItems()->whereIn('product_sku_id', $sku_ids)->delete();
    }
}
