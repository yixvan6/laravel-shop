<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $builder = Product::query()->where('on_sale', true);

        // 若有搜索字段
        if ($search = $request->search) {
            $like = '%'.$search.'%';
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 若有排序参数
        if ($order = $request->order) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);
        // 过滤的参数
        $filters = [
            'order' => $order,
            'search' => $search,
        ];

        return view('products.index', compact('products', 'filters'));
    }

    public function show(Request $request, Product $product)
    {
        // 检查商品是否上架
        if (! $product->on_sale) {
            throw new InvalidRequestException('该商品未上架');
        }

        // 商品收藏状态
        $favored = false;
        if ($user = $request->user()) {
            $favored = boolval($user->favorites()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        return view('products.show', compact('product', 'favored', 'reviews'));
    }

    public function favor(Request $request, Product $product)
    {
        $user = $request->user();
        // 若已收藏过，则不作处理
        if ($user->favorites()->find($product->id)) {
            return [];
        }

        $user->favorites()->attach($product);

        return [];
    }

    public function disfavor(Request $request, Product $product)
    {
        $user = $request->user();
        // 如果没有收藏，则不作任何操作
        if (! $user->favorites()->find($product->id)) {
            return [];
        }

        $user->favorites()->detach($product);

        return [];
    }

    public function favoritesIndex(Request $request)
    {
        $products = $request->user()->favorites()->paginate(16);

        return view('products.favorites', compact('products'));
    }
}
