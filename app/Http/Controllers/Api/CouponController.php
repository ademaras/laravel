<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCoupon;
use App\Models\Coupons;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function list()
    {
        $coupons = UserCoupon::where('user_id', Auth::id())->get();
        return $this->mobile(true, 'Kuponlar', $coupons);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'percent' => 'required|numeric',
            'amount' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $coupon = new UserCoupon();
        $coupon->user_id = Auth::id();
        $coupon->name = $request->name;
        $coupon->percent = $request->percent;
        $coupon->amount = $request->amount;
        $coupon->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $coupon->end_date = Carbon::parse($request->end_date)->format('Y-m-d');
        $coupon->description = $request->description;
        $coupon->save();

        return $this->mobile(true, 'Kupon eklendi', $coupon);
    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'percent' => 'required|numeric',
            'amount' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $coupon = UserCoupon::find($id);
        if ($coupon) {
            $coupon->name = $request->name;
            $coupon->percent = $request->percent;
            $coupon->amount = $request->amount;
            $coupon->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $coupon->end_date = Carbon::parse($request->end_date)->format('Y-m-d');
            $coupon->description = $request->description;
            $coupon->save();

            return $this->mobile(true, 'Kupon güncellendi', $coupon);
        } else {
            return $this->mobile(false, 'Kupon bulunamadı');
        }
    }

    public function delete($id)
    {
        $coupon = UserCoupon::find($id);
        if ($coupon) {
            $coupon->delete();
            return $this->mobile(true, 'Kupon silindi');
        } else {
            return $this->mobile(false, 'Kupon bulunamadı');
        }
    }

    public function dijiCoupons()
    {
        $coupons = Coupons::all();

        $data = [];

        foreach ($coupons as $key => $value) {
            $data[$key] = $value;
            $data[$key]['start_date'] = Carbon::parse($value->start_date)->format('d M Y');
            $data[$key]['end_date'] = Carbon::parse($value->end_date)->format('d M Y');
        }

        return $this->mobile(true, 'Coupons', $coupons);
    }
}
