<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsed extends Model
{
    use HasFactory;

    protected $table = 'coupon_useds';
    protected $guarded = ['id'];

    public function coupon()
    {
        return $this->belongsTo(Coupons::class, 'coupon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
