<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function coupon_index()
    {
        $coupons = Coupons::all();
        return view('admin.coupon.list', [
            'coupons' => $coupons,
        ]);
    }

    public function coupon_create()
    {
        return view('admin.coupon.create');
    }

    public function coupon_create_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start-date' => 'required|date',
            'end-date' => 'required|date',
            'usage_area' => 'required|in:0,1,2',
            'type' => 'required|in:0,1',
            'value' => 'required|numeric',
            'code' => 'required',
            'users' => 'required|in:0,1,2,3,4',
            'desc' => 'required',
            'status' => 'required|in:0,1',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $coupon = new Coupons();
            $coupon->name = $request->input('name');
            $coupon->start_date = $request->input('start-date');
            $coupon->end_date = $request->input('end-date');
            $coupon->usage_area = $request->input('usage_area');
            $coupon->type = $request->input('type');
            $coupon->value = $request->input('value');
            $coupon->code = $request->input('code');
            $coupon->user_type = $request->input('users');
            $coupon->description = $request->input('dsc');
            $coupon->status = $request->input('status');
            $coupon->save();
            return redirect()->route('admin.coupon.index')->with('success', 'Bağlantı oluşturma işlemi başarılı.');
        }
    }

    public function coupon_edit($id)
    {
        $coupon = Coupons::find($id);
        return view('admin.coupon.edit', [
            'coupon' => $coupon,
        ]);
    }

    public function coupon_edit_post(Request $request)
    {

        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start-date' => 'required|date',
            'end-date' => 'required|date',
            'usage_area' => 'required|in:0,1,2',
            'type' => 'required|in:0,1',
            'value' => 'required|numeric',
            'code' => 'required',
            'users' => 'required|in:0,1,2,3,4',
            'desc' => 'required',
            'status' => 'required|in:0,1',
        ]);


        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $coupon = Coupons::find($request->input('coupon_id'));
            $coupon->name = $request->input('name');
            $coupon->start_date = $request->input('start-date');
            $coupon->end_date = $request->input('end-date');
            $coupon->usage_area = $request->input('usage_area');
            $coupon->type = $request->input('type');
            $coupon->value = $request->input('value');
            $coupon->code = $request->input('code');
            $coupon->user_type = $request->input('users');
            $coupon->description = $request->input('desc');
            $coupon->status = $request->input('status');
            $coupon->save();
            return redirect()->route('admin.coupon.index')->with('success', 'Kupon güncelleme işlemi başarılı.');
        }
    }

    public function coupon_delete($id)
    {
        $coupon = Coupons::find($id);

        if (!$coupon) {
            return redirect()->back()->with('Kupon bulunamadı!');
        } else {
            $coupon->delete();
            return redirect()->back()->with('Kupon başarıyla silindi.');
        }
    }
}
