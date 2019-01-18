<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;

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
}
