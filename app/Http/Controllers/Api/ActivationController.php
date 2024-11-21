<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cards;
use App\Models\DijicardCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ActivationController extends Controller
{
    public function category()
    {
        $dijicardCategories = DijicardCategory::get();
        return $this->mobile(true, 'Ürün Kategorileri', $dijicardCategories);
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|exists:dijicard_categories,name',
            'code' => 'required|exists:cards,activation_code',
        ], [
            'code.required' => 'Kod alanı zorunludur.',
            'code.exists' => 'Girdiğiniz kod mevcut değil.',
            'type.exists' => 'Girilen ürün türü sistemimizde bulunmamaktadır.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $card = Cards::where('activation_code', $request->code)->first();

        if ($card) {
            if ($card->used == 0) {
                $card->user_id = Auth::user()->id;
                $card->used = 1;
                $card->save();
                return $this->mobile(true, 'Kart başarıyla etkinleştirildi');
            } else {
                return $this->mobile(false, 'Kart zaten etkinleştirilmiş');
            }
        } else {
            return $this->mobile(false, 'Kart bulunamadı');
        }
    }
}
