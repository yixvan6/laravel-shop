<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
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

        return view('products.show', compact('product'));
    }
}
