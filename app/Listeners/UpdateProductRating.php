<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

class UpdateProductRating implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        $items = $event->getOrder()->items()->with(['product'])->get();

        foreach ($items as $item) {
            $product = $item->product;
            $res = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereNotNull('reviewed_at')
                ->first([
                    \DB::raw('count(*) as review_count'),
                    \DB::raw('avg(rating) as rating')
                ]);

            $product->update([
                'rating' => $res->rating,
                'review_count' => $res->review_count,
            ]);
        }
    }
}
