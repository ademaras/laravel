<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Campaign;
use App\Models\Coupons;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $normal_count = User::where('user_type', 1)->count();
        $business_count = User::where('user_type', 2)->count();
        $premium_count = User::where('user_type', 3)->count();
        $campaign_count = Campaign::count();
        $coupon_count = Coupons::count();
        $notify_count = AdminNotification::count();
        return view('admin.dashboard.index', [
            'normal' => $normal_count,
            'business' => $business_count,
            'premium' => $premium_count,
            'campaign' => $campaign_count,
            'coupon' => $coupon_count,
            'notify' => $notify_count,
        ]);
    }
}
