<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PacketController extends Controller
{
    public function list()
    {
        $packets = Auth::user()->packets()->get();

        $data = [];

        foreach ($packets as $key => $value) {
            $data['active'] = Auth::user()->packets()->where('end_date', '>', Carbon::now())->first();
            $data['olds'] = Auth::user()->packets()->where('end_date', '<', Carbon::now())->get();
        }

        if (count($data) < 1) {
            return $this->mobile(true, 'Paket listesi');
        }
        return $this->mobile(true, 'Paket listesi', $data);
    }
}
