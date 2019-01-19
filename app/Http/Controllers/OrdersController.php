<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;

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
}
