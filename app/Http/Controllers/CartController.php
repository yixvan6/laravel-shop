<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\ProductSku;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function add(AddCartRequest $request)
    {
        $this->cartService->add($request->sku_id, $request->amount);

        return [];
    }

    public function index()
    {
        $cartItems = $this->cartService->get();
        $addresses = \Auth::user()->addresses;

        return view('cart.index', compact('cartItems', 'addresses'));
    }

    public function remove(ProductSku $sku)
    {
        $this->cartService->remove($sku->id);

        return [];
    }
}
