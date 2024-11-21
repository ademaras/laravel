<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserCardSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SignatureController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->corporateSignature()->exists()) {
            $user->corporateSignature()->create([
                'user_id' => $user->id,
                'card_id' => $user->businessCard()->first()->id ?? null,
            ]);
        }
        return view('user.dashboard.signature');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_imagec' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'company_imagec' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'dijicode_imagec' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'name' => 'nullable|string',
            'job' => 'nullable',
            'company' => 'nullable',
            'phone' => 'nullable',
            'location' => 'nullable',
            'dijicode' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $user = User::find(Auth::user()->id);

        if (!$user->corporateSignature()->exists()) {
            $user->corporateSignature()->create([
                'user_id' => $user->id,
                'card_id' => $user->businessCard()->first()->id ?? null,
            ]);
        }

        $aboutConf = $user->corporateSignature()->first();

        $aboutConf->update([
            'name' => $request->name,
            'job' => $request->job,
            'company' => $request->company,
            'phone' => $request->phone,
            'location' => $request->location,
            'dijicode' => $request->dijicode ?? 0,
        ]);

        if ($request->hasFile('profile_imagec')) {
            $profile_image = $request->file('profile_imagec');
            $profile_image_name = time() . uniqid() . '_' . $profile_image->getClientOriginalName();
            $profile_image->storeAs('public/signatures', $profile_image_name);
            $profile_image = 'storage/signatures/' . $profile_image_name;

            $aboutConf->update([
                'profile_image' => $profile_image,
            ]);
        }

        if ($request->hasFile('company_imagec')) {
            $company_image = $request->file('company_imagec');
            $company_image_name = time() . uniqid() . '_' . $company_image->getClientOriginalName();
            $company_image->storeAs('public/signatures', $company_image_name);
            $company_image = 'storage/signatures/' . $company_image_name;

            $aboutConf->update([
                'company_image' => $company_image,
            ]);
        }

        if ($request->hasFile('dijicode_imagec')) {
            $dijicode_image = $request->file('dijicode_imagec');
            $dijicode_image_name = time() . uniqid() . '_' . $dijicode_image->getClientOriginalName();
            $dijicode_image->storeAs('public/signatures', $dijicode_image_name);
            $dijicode_image = 'storage/signatures/' . $dijicode_image_name;

            $aboutConf->update([
                'dijicode_image' => $dijicode_image,
            ]);
        }

        return back();
    }
}
