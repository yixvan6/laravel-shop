<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use App\Http\Requests\ReviewRequest;
use App\Events\OrderReviewed;
use App\Http\Requests\ApplyRefundRequest;
use App\Models\CouponCode;
use App\Exceptions\CouponUnavailableException;

class OrdersController extends Controller
{
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $address = UserAddress::find($request->address_id);
        $coupon = null;
        if ($code = $request->coupon_code) {
            if (! $coupon = CouponCode::where('code', $code)->first()) {
                throw new CouponUnavailableException('优惠券不存在');
            }
        }

        return $orderService->store($address, $request->remark, $request->items, $coupon);
    }

    public function index()
    {
        $orders = Order::query()
            ->where('user_id', \Auth::id())
            ->with(['items.product', 'items.productSku'])
            ->latest()
            ->paginate();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('own', $order);

        $order->load(['items.product', 'items.productSku']);

        return view('orders.show', compact('order'));
    }

    public function received(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);

        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        // 返回原页面
        return $order;
    }

    public function review(Order $order)
    {
        $this->authorize('own', $order);

        if (! $order->paid_at) {
            throw new InvalidRequestException('订单未支付，不可评价');
        }

        $order->load(['items.product', 'items.productSku']);

        return view('orders.review', compact('order'));
    }

    public function reviewStore(ReviewRequest $request, Order $order)
    {
        $this->authorize('own', $order);

        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }

        $reviews = $request->reviews;

        \DB::transaction(function () use ($reviews, $order) {
            foreach ($reviews as $review) {
                $item = $order->items()->find($review['id']);
                $item->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            $order->update(['reviewed' => true]);

            event(new OrderReviewed($order));
        });

        return redirect()->back();
    }

    public function applyRefund(ApplyRefundRequest $request, Order $order)
    {
        $this->authorize('own', $order);
        // 判断订单是否已付款
        if (! $order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可退款');
        }
        // 判断订单退款状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }

        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->reason;

        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra' => $extra,
        ]);

        return $order;
    }
}
