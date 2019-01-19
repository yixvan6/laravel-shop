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

class OrdersController extends Controller
{
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $address = UserAddress::find($request->address_id);

        return $orderService->store($address, $request->remark, $request->items);
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
}
