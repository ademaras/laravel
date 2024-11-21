<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class YoutubeController extends Controller
{
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = User::find(Auth::id());
        $user->youtube_url = $request->url;
        $user->save();

        return $this->mobile(true, 'Youtube URL başarıyla güncellendi');
    }

    public function remove()
    {
        $user = User::find(Auth::id());
        $user->youtube_url = null;
        $user->save();

        return $this->mobile(true, 'Youtube URL başarıyla kaldırıldı');
    }
}
