<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Background;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BackgroundController extends Controller
{
    public function index()
    {
        $backgrounds = Background::all();
        $userbg = Background::where('user_id', Auth::user()->id)->get();
        $bgimages = $userbg->merge($backgrounds);

        return view('user.dashboard.bg', compact('bgimages'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_color' => 'nullable',
            'bg_qr' => 'nullable|int|in:0,1',
            'show_name' => 'nullable|int|in:0,1',
            'show_company' => 'nullable|int|in:0,1',
            'show_job' => 'nullable|int|in:0,1',
            'show_location' => 'nullable|int|in:0,1',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'bg_id' => 'required_if:image,null|int|exists:backgrounds,id',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $user = User::find(Auth::user()->id);

        if (!$user->bgConfigc()->exists()) {
            $user->bgConfigc()->create([
                'user_id' => $user->id,
            ]);
        }

        $image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/backgrounds', $image_name);
            $image = 'storage/backgrounds/' . $image_name;

            $bgCreated = Background::create([
                'user_id' => $user->id,
                'name' => $user->name . ' Background Custom: ' . time(),
                'image' => $image,
            ]);
        }

        if ($image != null) {
            $user->bgConfigc()->update([
                'bg_id' => $bgCreated->id,
                'qr_color' => $request->qr_color,
                'bg_qr' => $request->bg_qr,
                'show_name' => $request->show_name,
                'show_company' => $request->show_company,
                'show_job' => $request->show_job,
                'show_location' => $request->show_location,
            ]);
        } else {
            $user->bgConfigc()->update([
                'bg_id' => $request->bg_id,
                'qr_color' => $request->qr_color,
                'bg_qr' => $request->bg_qr,
                'show_name' => $request->show_name,
                'show_company' => $request->show_company,
                'show_job' => $request->show_job,
                'show_location' => $request->show_location,
            ]);
        }

        return $this->response(true, 'Background updated successfully');
    }
}
