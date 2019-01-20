<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CouponCode;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;

class CouponCodesController extends Controller
{
    public function show(string $code)
    {
        if (! $record = CouponCode::where('code', $code)->first()) {
            abort(404);
        }

        if (! $record->enabled) {
            abort(404);
        }

        if ($record->total - $record->used <= 0) {
            return response()->json(['msg' => '该优惠券已被兑完'], 403);
        }

        if ($record->not_after && $record->not_after->lt(Carbon::now())) {
            return response()->json(['msg' => '优惠券已过期'], 403);
        }

        if ($record->not_before && $record->not_before->gt(Carbon::now())) {
            return response()->json(['msg' => '优惠券还未到使用时间'], 403);
        }

        return $record;
    }
}
