<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'province' => 'required',
                'district' => 'required',
                'state' => 'required',
                'open_address' => 'required',
                'title' => 'required',
            ],
            [
                'province.required' => 'İl boş olamaz',
                'district.required' => 'İlçe boş olamaz',
                'state.required' => 'Mahalle boş olamaz',
                'open_address.required' => 'Adres boş olamaz',
                'title.required' => 'Başlık boş olamaz',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator, 'address_error')->withInput();
        }

        Address::create([
            'user_id' => Auth::user()->id,
            'province' => $request->province,
            'district' => $request->district,
            'state' => $request->state,
            'open_address' => $request->open_address,
            'title' => $request->title,
        ]);

        return redirect()->back()->with('success', 'Adres başarıyla oluşturuldu');
    }
}
