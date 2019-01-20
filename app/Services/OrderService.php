<?php

namespace App\Services;

use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Jobs\CloseOrder;
use Carbon\Carbon;
use App\Exceptions\InvalidRequestException;
use App\Services\CartService;
use App\Models\CouponCode;
use App\Exceptions\CouponUnavailableException;

class OrderService
{
    public function store(UserAddress $address, $remark, $items, $coupon = null)
    {
        $user = \Auth::user();

        if ($coupon) {
            $coupon->check();
        }

        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items, $coupon) {
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $remark,
                'total_amount' => 0,
            ]);
            // 关联用户后保存
            $order->user()->associate($user);
            $order->save();

            // 遍历 items 写入 order_items 表
            $total_amount = 0;
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $total_amount += $sku->price * $data['amount'];
                // 减去对应商品的库存量
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }
            if ($coupon) {
                $coupon->check($total_amount);
                $total_amount = $coupon->getAdjustedPrice($total_amount);
                $order->couponCode()->associate($coupon);
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponUnavailableException('优惠券已被兑完');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $total_amount]);

            // 将下单商品从购物车中移除
            $sku_ids = collect($items)->pluck('sku_id');
            app(CartService::class)->remove($sku_ids);

            return $order;
        });

        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}
