<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use App\Exceptions\CouponUnavailableException;

class CouponCodesController extends Controller
{
    public function show(string $code)
    {
        if (! $record = CouponCode::where('code', $code)->first()) {
            throw new CouponUnavailableException('优惠券不存在');
        }

        $record->check(\Auth::user());

        return $record;
    }
}
