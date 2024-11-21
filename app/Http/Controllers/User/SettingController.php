<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Faq;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses;
        $packets = $user->packets;

        $faqs = Faq::get();
        $contracts = Contract::get();
        return view('user.setting.index', compact('user', 'addresses', 'packets', 'faqs', 'contracts'));
    }
}
