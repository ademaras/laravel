<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AboutController extends Controller
{
    public function aboutPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'profile_img' => 'nullable',
            'bg_img' => 'nullable',
            'corporate_img' => 'nullable',
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|max:255',
            'job' => 'nullable|max:255',
            'company_name' => 'nullable|max:255',
            'about' => 'nullable|max:1000',
        ]);

        $user = User::find(Auth::user()->id);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        if (!$user->personalCard()->exists()) {
            $user->personalCard()->create([
                'user_id' => $user->id,
            ]);
        }

        if (!$user->userBasic()->exists()) {
            $user->userBasic()->create([
                'card_id' => $user->personalCard->id,
            ]);
        }

        if ($request->hasFile('profile_img')) {
            $image = $request->file('profile_img');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $imagename = 'storage/profiles/' . $image_name;

            $user->userBasic()->update([
                'profile_img' => $imagename,
            ]);
        }

        if ($request->hasFile('bg_img')) {
            $image = $request->file('bg_img');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profilesbg', $image_name);
            $imagename = 'storage/profilesbg/' . $image_name;

            $user->userBasic()->update([
                'bg_img' => $imagename,
            ]);
        }

        if ($request->hasFile('corporate_img')) {
            $image = $request->file('corporate_img');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profilescorporate', $image_name);
            $imagename = 'storage/profilescorporate/' . $image_name;

            $user->userBasic()->update([
                'corporate_img' => $imagename,
            ]);
        }

        $userName = strtolower(str_replace([' ', 'ç', 'ğ', 'ı', 'i', 'ö', 'ş', 'ü'], ['', 'c', 'g', 'i', 'i', 'o', 's', 'u'], $request->title));

        $count = User::where('username', $userName)->count();
        if ($count > 0) {
            $username = $userName . ($count + 1);
        }

        $user->userBasic()->update([
            'title' => $userName,
            'name' => $request->name,
            'location' => $request->location,
            'job' => $request->job,
            'company_name' => $request->company_name,
            'about' => $request->about,
        ]);

        return back();
    }
}
